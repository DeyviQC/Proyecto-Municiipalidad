<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->isCiudadano();
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['NACIMIENTO', 'MATRIMONIO', 'DEFUNCION'])],
            'titular' => ['required', 'string', 'max:200'],
            'dni_titular' => ['nullable', 'regex:/^[0-9]{8}$/'],
            'fecha_acta' => ['nullable', 'date', 'before_or_equal:today'],
            'detalle' => ['required', 'string', 'max:2000'],
            'version' => [$this->isMethod('patch') ? 'required' : 'nullable', 'integer', 'min:1'],
        ];
    }
}
