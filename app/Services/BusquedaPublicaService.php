<?php

namespace App\Services;

use App\Models\Acta;
use Illuminate\Support\Str;

class BusquedaPublicaService
{
    public function buscar(array $datos): ?Acta
    {
        [$relacion, $fecha] = match ($datos['tipo']) {
            'NACIMIENTO' => ['nacimiento', 'fecha_nacimiento'],
            'MATRIMONIO' => ['matrimonio', 'fecha_matrimonio'],
            'DEFUNCION' => ['defuncion', 'fecha_defuncion'],
        };
        $candidatas = Acta::with($relacion)->where('tipo', $datos['tipo'])->where('estado', Acta::ESTADO_REGISTRADA)
            ->whereHas($relacion, fn ($q) => $q->whereDate($fecha, $datos['fecha']))->get();
        $buscado = $this->normalizar($datos['titular']);
        $coinciden = $candidatas->filter(fn ($acta) => $this->normalizar($acta->$relacion->apellido_nombre) === $buscado);
        if ($coinciden->count() !== 1) return null;
        $acta = $coinciden->first();
        return $acta->pdf_ruta && app(PdfStorageService::class)->existe($acta->pdf_ruta) ? $acta : null;
    }

    private function normalizar(string $nombre): string
    {
        $partes = preg_split('/[^A-Z0-9]+/', strtoupper(Str::ascii($nombre)), -1, PREG_SPLIT_NO_EMPTY);
        sort($partes);
        return implode(' ', $partes);
    }
}
