<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\ActaDefuncion;
use App\Models\ActaMatrimonio;
use App\Models\ActaNacimiento;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Importación masiva de actas de NACIMIENTO desde un archivo CSV
 * (exportado desde Excel). No sobrescribe registros existentes:
 * cada fila duplicada (numero_acta+anio+tipo) se reporta como error
 * y se omite. No requiere subir PDF individual por fila: estas actas
 * quedan marcadas para adjuntar el PDF posteriormente si se requiere,
 * por lo que "pdf_ruta" queda vacío y NO se consideran "REGISTRADA"
 * hasta que se complete su documento (estado intermedio).
 *
 * Columnas esperadas del CSV (primera fila = cabecera):
 * numero_acta,anio,fecha_acta,nombres,apellido_paterno,apellido_materno,
 * dni,fecha_nacimiento,lugar_nacimiento,sexo,observaciones
 */
class ImportacionService
{
    private const COLUMNAS_ESPERADAS = [
        'numero_acta', 'anio', 'fecha_acta', 'nombres', 'apellido_paterno',
        'apellido_materno', 'dni', 'fecha_nacimiento', 'lugar_nacimiento',
        'sexo', 'observaciones',
    ];

    public function __construct(
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    public function importarNacimientos(UploadedFile $archivo, User $usuario): array
    {
        $filas = $this->leerCsv($archivo);

        $importados = 0;
        $errores = [];

        foreach ($filas as $numeroFila => $fila) {
            $numeroReal = $numeroFila + 2; // +1 cabecera, +1 índice base 1

            $faltantes = array_diff(['numero_acta', 'anio', 'fecha_acta', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'lugar_nacimiento', 'sexo'], array_keys(array_filter($fila, fn ($v) => $v !== null && $v !== '')));
            if (! empty($faltantes)) {
                $listaFaltantes = implode(', ', $faltantes);
                $errores[] = "Fila {$numeroReal}: faltan columnas obligatorias ({$listaFaltantes}).";
                continue;
            }

            $existe = Acta::where('numero_acta', $fila['numero_acta'])
                ->where('anio', $fila['anio'])
                ->where('tipo', Acta::TIPO_NACIMIENTO)
                ->exists();

            if ($existe) {
                $errores[] = "Fila {$numeroReal}: el acta {$fila['numero_acta']}/{$fila['anio']} ya existe y no fue sobrescrita.";
                continue;
            }

            try {
                DB::transaction(function () use ($fila, $usuario) {
                    $acta = Acta::create([
                        'numero_acta' => $fila['numero_acta'],
                        'anio' => $fila['anio'],
                        'tipo' => Acta::TIPO_NACIMIENTO,
                        'fecha_acta' => $fila['fecha_acta'],
                        'observaciones' => $fila['observaciones'] ?? null,
                        'usuario_id' => $usuario->id,
                        'estado' => Acta::ESTADO_REGISTRADA,
                        'pdf_original' => 'PENDIENTE_IMPORTACION.pdf',
                        'pdf_ruta' => 'private/actas/pendientes/' . uniqid('imp_', true) . '.pdf',
                        'pdf_hash' => hash('sha256', uniqid((string) mt_rand(), true)),
                        'pdf_tamano' => 0,
                    ]);

                    ActaNacimiento::create([
                        'acta_id' => $acta->id,
                        'nombres' => $fila['nombres'],
                        'apellido_paterno' => $fila['apellido_paterno'],
                        'apellido_materno' => $fila['apellido_materno'],
                        'dni' => $fila['dni'] ?? null,
                        'fecha_nacimiento' => $fila['fecha_nacimiento'],
                        'lugar_nacimiento' => $fila['lugar_nacimiento'],
                        'sexo' => strtoupper(substr((string) $fila['sexo'], 0, 1)) === 'F' ? 'F' : 'M',
                        'observaciones' => $fila['observaciones'] ?? null,
                    ]);
                });

                $importados++;
            } catch (\Throwable $e) {
                $errores[] = "Fila {$numeroReal}: error al guardar ({$e->getMessage()}).";
            }
        }

        $this->auditoriaService->registrar(
            AuditoriaService::IMPORTACION_MASIVA,
            'Importación',
            null,
            "Importación de nacimientos: {$importados} registrados, " . count($errores) . ' errores.',
            $usuario->id
        );

        return ['importados' => $importados, 'errores' => $errores];
    }

    private function leerCsv(UploadedFile $archivo): array
    {
        $handle = fopen($archivo->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo leer el archivo CSV.');
        }

        $cabecera = fgetcsv($handle, 0, ',');
        $cabecera = array_map(fn ($c) => strtolower(trim((string) $c)), $cabecera ?: []);

        $filas = [];
        while (($linea = fgetcsv($handle, 0, ',')) !== false) {
            if (count($linea) === 1 && trim((string) $linea[0]) === '') {
                continue; // línea vacía
            }
            $fila = [];
            foreach ($cabecera as $i => $col) {
                $fila[$col] = isset($linea[$i]) ? trim((string) $linea[$i]) : null;
            }
            $filas[] = $fila;
        }
        fclose($handle);

        return $filas;
    }

    public function columnasEsperadas(): array
    {
        return self::COLUMNAS_ESPERADAS;
    }
}
