<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvaliacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agendamentoId' => ['required', 'uuid'],
            'nota' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'agendamentoId.required' => 'O ID do agendamento é obrigatório.',
            'nota.required' => 'A nota é obrigatória.',
            'nota.min' => 'A nota deve estar entre 1 e 5.',
            'nota.max' => 'A nota deve estar entre 1 e 5.',
        ];
    }
}
