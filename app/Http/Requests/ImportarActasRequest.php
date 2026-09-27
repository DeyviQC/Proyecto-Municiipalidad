<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarActasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.mimes' => 'El archivo debe ser un CSV (exportado desde Excel).',
        ];
    }
}
