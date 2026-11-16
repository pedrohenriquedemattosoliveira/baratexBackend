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
            'preco' => ['required', 'numeric'],
            'tempoEstimadoMinutos' => ['nullable', 'integer'],
            'oficinaId' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'preco.required' => 'O campo preco é obrigatório.',
            'oficinaId.required' => 'O campo oficinaId é obrigatório.',
        ];
    }
}
