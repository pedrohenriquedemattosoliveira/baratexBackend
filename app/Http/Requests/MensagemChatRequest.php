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
            'agendamentoId' => ['required', 'uuid'],
            'remetenteId' => ['required', 'uuid'],
            'conteudo' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'agendamentoId.required' => 'O ID do agendamento é obrigatório.',
            'remetenteId.required' => 'O ID do remetente é obrigatório.',
            'conteudo.required' => 'O conteúdo da mensagem é obrigatório.',
        ];
    }
}
