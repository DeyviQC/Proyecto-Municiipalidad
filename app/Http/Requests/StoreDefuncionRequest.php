<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDefuncionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['required', 'string', 'max:80'],
            'dni' => ['nullable', 'string', 'max:15'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'fecha_defuncion' => ['required', 'date', 'before_or_equal:today'],
            'lugar_defuncion' => ['required', 'string', 'max:150'],
            'causa_defuncion' => ['required', 'string', 'max:200'],
            'medico_certifica' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
