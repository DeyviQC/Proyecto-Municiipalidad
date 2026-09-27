<?php

namespace App\Http\Requests;

use App\Models\Acta;
use Illuminate\Foundation\Http\FormRequest;

class StoreActaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Acta::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'in:NACIMIENTO,MATRIMONIO,DEFUNCION'],
            'numero_acta' => ['required', 'string', 'max:20'],
            'anio' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'fecha_acta' => ['required', 'date', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:' . (int) config('actas.pdf_max_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf.required' => 'Debe adjuntar el PDF escaneado del acta.',
            'pdf.mimes' => 'El archivo debe ser un PDF.',
            'pdf.max' => 'El PDF supera el tamaño máximo permitido.',
            'fecha_acta.before_or_equal' => 'La fecha del acta no puede ser futura.',
        ];
    }
}
