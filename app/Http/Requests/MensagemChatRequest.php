<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MensagemChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agendamentoId' => ['required', 'uuid', 'exists:agendamentos,id'],
            // Mantido só por compatibilidade com a API original: o
            // remetente gravado é sempre o usuário autenticado (JWT).
            'remetenteId' => ['nullable', 'uuid'],
            'conteudo' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'agendamentoId.required' => 'O ID do agendamento é obrigatório.',
            'agendamentoId.exists' => 'Agendamento não encontrado.',
            'conteudo.required' => 'O conteúdo da mensagem é obrigatório.',
        ];
    }
}
