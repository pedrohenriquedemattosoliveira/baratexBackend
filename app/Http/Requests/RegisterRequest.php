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

            // Auto-cadastro de prestador: quando tipoUsuario=OFICINA, o
            // bloco "oficina" cria a Oficina + Endereco já vinculados ao
            // usuário. É opcional (um OFICINA sem esse bloco fica só com o
            // usuário, como na API original), mas se vier precisa estar
            // completo.
            'oficina' => ['nullable', 'array'],
            'oficina.razaoSocial' => ['required_with:oficina', 'string'],
            'oficina.nomeFantasia' => ['required_with:oficina', 'string'],
            'oficina.cnpj' => ['required_with:oficina', 'string'],
            'oficina.endereco' => ['required_with:oficina', 'array'],
            'oficina.endereco.logradouro' => ['required_with:oficina', 'string'],
            'oficina.endereco.numero' => ['required_with:oficina', 'string'],
            'oficina.endereco.complemento' => ['nullable', 'string'],
            'oficina.endereco.bairro' => ['required_with:oficina', 'string'],
            'oficina.endereco.cidade' => ['required_with:oficina', 'string'],
            'oficina.endereco.estado' => ['required_with:oficina', 'string', 'size:2'],
            'oficina.endereco.cep' => ['required_with:oficina', 'string'],
            'oficina.endereco.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'oficina.endereco.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'oficina.razaoSocial' => 'razão social',
            'oficina.nomeFantasia' => 'nome fantasia',
            'oficina.cnpj' => 'CNPJ',
            'oficina.endereco' => 'endereço',
            'oficina.endereco.logradouro' => 'logradouro',
            'oficina.endereco.numero' => 'número',
            'oficina.endereco.bairro' => 'bairro',
            'oficina.endereco.cidade' => 'cidade',
            'oficina.endereco.estado' => 'estado (UF)',
            'oficina.endereco.cep' => 'CEP',
            'oficina.endereco.latitude' => 'latitude',
            'oficina.endereco.longitude' => 'longitude',
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
            'required_with' => 'O campo :attribute é obrigatório.',
            'oficina.endereco.estado.size' => 'O estado deve ser a sigla com 2 letras (ex: SP).',
            'between' => 'O campo :attribute está fora do intervalo permitido.',
        ];
    }
}
