<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotoristaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cpf' => ['required', 'regex:/^\d{11}$/'],
            'usuario' => ['required', 'array'],
            'usuario.nome' => ['required', 'string'],
            'usuario.email' => ['required', 'email'],
            'usuario.senhaHash' => ['required', 'string', 'min:6'],
            'usuario.telefone' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.regex' => 'O CPF deve conter exatamente 11 dígitos numéricos.',
            'usuario.required' => 'Os dados de usuário são obrigatórios.',
            'usuario.nome.required' => 'O nome é obrigatório.',
            'usuario.email.required' => 'O e-mail é obrigatório.',
            'usuario.email.email' => 'Formato de e-mail inválido.',
            'usuario.senhaHash.required' => 'A senha é obrigatória.',
            'usuario.senhaHash.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'usuario.telefone.required' => 'O telefone é obrigatório.',
        ];
    }
}
