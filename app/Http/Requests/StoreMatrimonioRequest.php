<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatrimonioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'c1_nombres' => ['required', 'string', 'max:100'],
            'c1_apellido_paterno' => ['required', 'string', 'max:80'],
            'c1_apellido_materno' => ['required', 'string', 'max:80'],
            'c1_dni' => ['nullable', 'string', 'max:15'],
            'c1_fecha_nacimiento' => ['required', 'date', 'before:today'],
            'c2_nombres' => ['required', 'string', 'max:100'],
            'c2_apellido_paterno' => ['required', 'string', 'max:80'],
            'c2_apellido_materno' => ['required', 'string', 'max:80'],
            'c2_dni' => ['nullable', 'string', 'max:15'],
            'c2_fecha_nacimiento' => ['required', 'date', 'before:today'],
            'fecha_matrimonio' => ['required', 'date', 'before_or_equal:today'],
            'lugar_matrimonio' => ['required', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
