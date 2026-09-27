<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\ActaDefuncion;
use App\Models\ActaMatrimonio;
use App\Models\ActaNacimiento;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActaService
{
    public function __construct(
        private readonly PdfValidationService $pdfValidationService,
        private readonly PdfStorageService $pdfStorageService,
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * Registra un acta completa (metadatos + detalle específico + PDF)
     * dentro de una única transacción. Si algo falla, se revierte todo,
     * incluyendo el archivo físico ya almacenado.
     *
     * @param  array  $datosActa  numero_acta, anio, tipo, fecha_acta, observaciones
     * @param  array  $datosDetalle  campos específicos de nacimiento/matrimonio/defunción
     */
    public function registrar(array $datosActa, array $datosDetalle, UploadedFile $pdf, User $usuario): Acta
    {
        $tipo = $datosActa['tipo'];
        $numeroActa = $datosActa['numero_acta'];
        $anio = (int) $datosActa['anio'];

        // 1. Validar PDF
        $resultado = $this->pdfValidationService->validar($pdf);
        if (! $resultado['valido']) {
            throw ValidationException::withMessages(['pdf' => $resultado['errores']]);
        }

        // 2. Verificar duplicado por número+año+tipo
        if ($this->pdfValidationService->existeDuplicadoPorNumero($numeroActa, $anio, $tipo)) {
            $this->auditoriaService->registrar(
                AuditoriaService::INTENTO_DUPLICADO,
                'Registro Civil',
                null,
                "Intento de registrar acta duplicada: {$tipo} {$numeroActa}/{$anio}",
                $usuario->id
            );

            throw ValidationException::withMessages([
                'numero_acta' => 'Ya existe un acta registrada con ese número, año y tipo.',
            ]);
        }

        // 3. Calcular hash y verificar duplicado de documento
        $hash = $this->pdfValidationService->calcularHash($pdf);
        if ($this->pdfValidationService->existeDuplicadoPorHash($hash)) {
            $this->auditoriaService->registrar(
                AuditoriaService::INTENTO_DUPLICADO,
                'Registro Civil',
                null,
                'Intento de subir un PDF que ya se encuentra registrado.',
                $usuario->id
            );

            throw ValidationException::withMessages([
                'pdf' => 'El documento PDF ya fue registrado en el sistema.',
            ]);
        }

        // 4. Guardar PDF en disco (fuera de la transacción de BD, pero
        //    con limpieza manual si la transacción falla).
        $archivoGuardado = $this->pdfStorageService->guardar($pdf, $tipo, $anio, $numeroActa);

        try {
            $acta = DB::transaction(function () use ($datosActa, $datosDetalle, $usuario, $hash, $archivoGuardado, $tipo) {
                $acta = Acta::create([
                    'numero_acta' => $datosActa['numero_acta'],
                    'anio' => $datosActa['anio'],
                    'tipo' => $tipo,
                    'fecha_acta' => $datosActa['fecha_acta'],
                    'observaciones' => $datosActa['observaciones'] ?? null,
                    'usuario_id' => $usuario->id,
                    'estado' => Acta::ESTADO_REGISTRADA,
                    'pdf_original' => $archivoGuardado['nombre_original'],
                    'pdf_ruta' => $archivoGuardado['ruta'],
                    'pdf_hash' => $hash,
                    'pdf_tamano' => $archivoGuardado['tamano'],
                ]);

                $this->crearDetalle($acta, $tipo, $datosDetalle);

                $this->auditoriaService->registrar(
                    AuditoriaService::CREAR_ACTA,
                    'Registro Civil',
                    $acta->id,
                    "Acta {$tipo} N° {$acta->numero_acta}/{$acta->anio} registrada.",
                    $usuario->id
                );

                return $acta;
            });
        } catch (\Throwable $e) {
            // Rollback manual del archivo físico si la transacción de BD falló.
            $this->pdfStorageService->eliminar($archivoGuardado['ruta']);
            throw $e;
        }

        return $acta->fresh(['nacimiento', 'matrimonio', 'defuncion', 'usuario']);
    }

    private function crearDetalle(Acta $acta, string $tipo, array $datos): void
    {
        match ($tipo) {
            Acta::TIPO_NACIMIENTO => ActaNacimiento::create(array_merge($datos, ['acta_id' => $acta->id])),
            Acta::TIPO_MATRIMONIO => ActaMatrimonio::create(array_merge($datos, ['acta_id' => $acta->id])),
            Acta::TIPO_DEFUNCION => ActaDefuncion::create(array_merge($datos, ['acta_id' => $acta->id])),
            default => throw new \InvalidArgumentException("Tipo de acta inválido: {$tipo}"),
        };
    }

    public function buscar(array $filtros)
    {
        $query = Acta::with(['nacimiento', 'matrimonio', 'defuncion', 'usuario']);

        if (! empty($filtros['numero_acta'])) {
            $query->where('numero_acta', 'like', '%' . $filtros['numero_acta'] . '%');
        }
        if (! empty($filtros['anio'])) {
            $query->where('anio', $filtros['anio']);
        }
        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        if (! empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_acta', '>=', $filtros['fecha_desde']);
        }
        if (! empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_acta', '<=', $filtros['fecha_hasta']);
        }
        if (! empty($filtros['dni']) || ! empty($filtros['nombre']) || ! empty($filtros['apellido'])) {
            $dni = $filtros['dni'] ?? null;
            $nombre = $filtros['nombre'] ?? null;
            $apellido = $filtros['apellido'] ?? null;

            $query->where(function ($q) use ($dni, $nombre, $apellido) {
                $q->whereHas('nacimiento', function ($sub) use ($dni, $nombre, $apellido) {
                    $this->aplicarFiltroPersona($sub, $dni, $nombre, $apellido, 'dni', 'nombres', ['apellido_paterno', 'apellido_materno']);
                })->orWhereHas('matrimonio', function ($sub) use ($dni, $nombre, $apellido) {
                    $sub->where(function ($s) use ($dni, $nombre, $apellido) {
                        $this->aplicarFiltroPersona($s, $dni, $nombre, $apellido, 'c1_dni', 'c1_nombres', ['c1_apellido_paterno', 'c1_apellido_materno']);
                    })->orWhere(function ($s) use ($dni, $nombre, $apellido) {
                        $this->aplicarFiltroPersona($s, $dni, $nombre, $apellido, 'c2_dni', 'c2_nombres', ['c2_apellido_paterno', 'c2_apellido_materno']);
                    });
                })->orWhereHas('defuncion', function ($sub) use ($dni, $nombre, $apellido) {
                    $this->aplicarFiltroPersona($sub, $dni, $nombre, $apellido, 'dni', 'nombres', ['apellido_paterno', 'apellido_materno']);
                });
            });
        }

        return $query->orderByDesc('fecha_acta')->paginate(15)->withQueryString();
    }

    private function aplicarFiltroPersona($query, ?string $dni, ?string $nombre, ?string $apellido, string $colDni, string $colNombre, array $colsApellido): void
    {
        if ($dni) {
            $query->where($colDni, 'like', "%{$dni}%");
        }
        if ($nombre) {
            $query->where($colNombre, 'like', "%{$nombre}%");
        }
        if ($apellido) {
            $query->where(function ($q) use ($apellido, $colsApellido) {
                foreach ($colsApellido as $col) {
                    $q->orWhere($col, 'like', "%{$apellido}%");
                }
            });
        }
    }
}
