<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNacimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorización real ya se valida en StoreActaRequest / ActaPolicy
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['required', 'string', 'max:80'],
            'dni' => ['nullable', 'string', 'max:15'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'hora_nacimiento' => ['nullable', 'date_format:H:i'],
            'lugar_nacimiento' => ['required', 'string', 'max:150'],
            'sexo' => ['required', 'in:M,F'],
            'nombre_padre' => ['nullable', 'string', 'max:150'],
            'dni_padre' => ['nullable', 'string', 'max:15'],
            'nombre_madre' => ['nullable', 'string', 'max:150'],
            'dni_madre' => ['nullable', 'string', 'max:15'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
