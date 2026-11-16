<?php

namespace App\Http\Requests;

use App\Enums\TipoUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string'],
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            'telefone' => ['nullable', 'string'],
            'cpf' => ['nullable', 'string'],
            'tipoUsuario' => ['required', Rule::in(array_column(TipoUsuario::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'E-mail inválido.',
            'senha.required' => 'A senha é obrigatória.',
            'tipoUsuario.required' => 'O tipo de usuário é obrigatório.',
            'tipoUsuario.in' => 'O tipo de usuário é obrigatório.',
        ];
    }
}
