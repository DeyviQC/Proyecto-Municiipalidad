<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('usuario')) ?? false;
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario')->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($usuarioId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuarioId)],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role' => ['required', 'in:administrador,asistente'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
