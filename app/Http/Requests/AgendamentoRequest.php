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
            'oficinaId' => ['required', 'uuid', 'exists:oficinas,id'],
            'veiculoId' => ['required', 'uuid', 'exists:tb_veiculo,id'],
            'dataHora' => ['required', 'date'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.servicoId' => ['required', 'uuid', 'exists:servicos,id'],
            'itens.*.precoPraticado' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'oficinaId.required' => 'O ID da oficina é obrigatório.',
            'oficinaId.exists' => 'Oficina não encontrada.',
            'veiculoId.exists' => 'Veículo não encontrado.',
            'itens.*.servicoId.exists' => 'Serviço não encontrado.',
            'veiculoId.required' => 'O ID do veículo é obrigatório.',
            'dataHora.required' => 'A data e hora do agendamento são obrigatórias.',
            'itens.required' => 'O agendamento deve conter ao menos um serviço.',
            'itens.min' => 'O agendamento deve conter ao menos um serviço.',
            'itens.*.servicoId.required' => 'O ID do serviço é obrigatório.',
        ];
    }
}
