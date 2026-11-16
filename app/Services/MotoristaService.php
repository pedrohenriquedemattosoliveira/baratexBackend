<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Exceptions\NegocioException;
use App\Models\Motorista;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MotoristaService
{
    public function salvar(array $dados): Motorista
    {
        return DB::transaction(function () use ($dados) {
            $usuario = Usuario::create([
                'nome' => $dados['usuario']['nome'],
                'email' => $dados['usuario']['email'],
                'senha_hash' => Hash::make($dados['usuario']['senhaHash']),
                'telefone' => $dados['usuario']['telefone'] ?? null,
                'tipo_usuario' => TipoUsuario::MOTORISTA,
            ]);

            return Motorista::create([
                'cpf' => $dados['cpf'],
                'usuario_id' => $usuario->id,
            ]);
        });
    }

    public function buscarPorId(string $id): Motorista
    {
        $motorista = Motorista::with('usuario')->find($id);

        if (! $motorista) {
            throw new NegocioException("Motorista não encontrado com o ID: {$id}");
        }

        return $motorista;
    }

    /**
     * Resolve o registro de Motorista do usuário autenticado — usado por
     * GET /api/motoristas/me, para o app não precisar conhecer o UUID do
     * motorista de antemão.
     */
    public function buscarPorUsuario(Usuario $usuario): Motorista
    {
        $motorista = Motorista::with('usuario')->where('usuario_id', $usuario->id)->first();

        if (! $motorista) {
            throw new NegocioException('Nenhum cadastro de motorista encontrado para este usuário.');
        }

        return $motorista;
    }
}
