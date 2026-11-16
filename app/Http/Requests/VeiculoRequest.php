<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'placa' => ['required', 'string'],
            'modeloId' => ['required', 'uuid'],
            'ano' => ['required', 'integer'],
            'cor' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'placa.required' => 'A placa é obrigatória.',
            'modeloId.required' => 'O ID do modelo é obrigatório.',
            'ano.required' => 'O ano é obrigatório.',
        ];
    }
}
