<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GaragemVirtualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agendamentoId' => ['required', 'uuid', 'exists:agendamentos,id'],
            'dataServico' => ['required', 'date'],
            'quilometragemRegistro' => ['required', 'integer', 'min:0'],
            'descricaoServico' => ['required', 'string'],
            'validadoOficina' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'agendamentoId.required' => 'O ID do agendamento é obrigatório.',
            'agendamentoId.exists' => 'Agendamento não encontrado.',
            'dataServico.required' => 'A data do serviço é obrigatória.',
            'quilometragemRegistro.required' => 'A quilometragem do registro é obrigatória.',
            'descricaoServico.required' => 'A descrição do serviço é obrigatória.',
        ];
    }
}
