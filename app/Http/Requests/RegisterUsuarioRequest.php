<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('password') && !$this->filled('senha')) {
            $this->merge([
                'senha' => $this->input('password'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'senha' => 'required|string|min:6',
            'telefone' => 'required|string|max:20',
            'data_nascimento' => 'required|date',
        ];
    }
}
