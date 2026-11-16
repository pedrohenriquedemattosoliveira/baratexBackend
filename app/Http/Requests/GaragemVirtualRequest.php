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
            'agendamentoId' => ['required', 'uuid'],
            'dataServico' => ['required', 'date'],
            'quilometragemRegistro' => ['required', 'integer'],
            'descricaoServico' => ['required', 'string'],
            'validadoOficina' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'agendamentoId.required' => 'O ID do agendamento é obrigatório.',
            'dataServico.required' => 'A data do serviço é obrigatória.',
            'quilometragemRegistro.required' => 'A quilometragem do registro é obrigatória.',
            'descricaoServico.required' => 'A descrição do serviço é obrigatória.',
        ];
    }
}
