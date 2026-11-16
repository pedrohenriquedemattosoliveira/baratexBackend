<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oficinaId' => ['required', 'uuid'],
            'veiculoId' => ['required', 'uuid'],
            'dataHora' => ['required', 'date'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.servicoId' => ['required', 'uuid'],
            'itens.*.precoPraticado' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'oficinaId.required' => 'O ID da oficina é obrigatório.',
            'veiculoId.required' => 'O ID do veículo é obrigatório.',
            'dataHora.required' => 'A data e hora do agendamento são obrigatórias.',
            'itens.required' => 'O agendamento deve conter ao menos um serviço.',
            'itens.min' => 'O agendamento deve conter ao menos um serviço.',
            'itens.*.servicoId.required' => 'O ID do serviço é obrigatório.',
        ];
    }
}
