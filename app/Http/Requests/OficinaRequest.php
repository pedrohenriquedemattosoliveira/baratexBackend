<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OficinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'razaoSocial' => ['required', 'string'],
            'nomeFantasia' => ['required', 'string'],
            'cnpj' => ['required', 'string'],
            'usuario' => ['required', 'array'],
            'usuario.nome' => ['required', 'string'],
            'usuario.email' => ['required', 'string'],
            'usuario.senhaHash' => ['required', 'string'],
            'usuario.telefone' => ['nullable', 'string'],
            'endereco' => ['required', 'array'],
            // Obrigatórios porque as colunas correspondentes em
            // "enderecos" são NOT NULL (ver migration); latitude e
            // longitude seguem opcionais, como no Endereco.java original.
            'endereco.logradouro' => ['required', 'string'],
            'endereco.numero' => ['required', 'string'],
            'endereco.bairro' => ['required', 'string'],
            'endereco.cidade' => ['required', 'string'],
            'endereco.estado' => ['required', 'string', 'size:2'],
            'endereco.cep' => ['required', 'string'],
            'endereco.latitude' => ['nullable', 'numeric'],
            'endereco.longitude' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'razaoSocial.required' => 'O campo razaoSocial é obrigatório.',
            'nomeFantasia.required' => 'O campo nomeFantasia é obrigatório.',
            'cnpj.required' => 'O campo cnpj é obrigatório.',
            'usuario.required' => 'Os dados de usuário são obrigatórios.',
            'usuario.nome.required' => 'O campo nome é obrigatório.',
            'usuario.email.required' => 'O campo email é obrigatório.',
            'usuario.senhaHash.required' => 'O campo senhaHash é obrigatório.',
            'endereco.required' => 'Os dados de endereço são obrigatórios.',
            'endereco.logradouro.required' => 'O logradouro é obrigatório.',
            'endereco.numero.required' => 'O número é obrigatório.',
            'endereco.bairro.required' => 'O bairro é obrigatório.',
            'endereco.cidade.required' => 'A cidade é obrigatória.',
            'endereco.estado.required' => 'O estado é obrigatório.',
            'endereco.estado.size' => 'O estado deve ser a sigla com 2 letras (ex: SP).',
            'endereco.cep.required' => 'O CEP é obrigatório.',
        ];
    }
}
