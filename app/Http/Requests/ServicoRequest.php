<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['required', 'numeric', 'min:0'],
            'tempoEstimadoMinutos' => ['nullable', 'integer', 'min:1'],
            // Opcional: sem ele, o serviço é criado na oficina do usuário
            // autenticado (app do prestador).
            'oficinaId' => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'preco.required' => 'O campo preco é obrigatório.',
        ];
    }
}
