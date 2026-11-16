<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Exceptions\NegocioException;
use App\Models\ModeloVeiculo;
use App\Models\Motorista;
use App\Models\Usuario;
use App\Models\Veiculo;

class VeiculoService
{
    public function cadastrarVeiculo(array $dados, Usuario $usuarioAutenticado): Veiculo
    {
        $motorista = $this->motoristaDoUsuario($usuarioAutenticado);

        $modelo = ModeloVeiculo::find($dados['modeloId']);

        if (! $modelo) {
            throw new NegocioException('Modelo de veículo não encontrado.');
        }

        $veiculo = Veiculo::create([
            'placa' => mb_strtoupper($dados['placa']),
            'ano' => $dados['ano'],
            'cor' => $dados['cor'] ?? null,
            'modelo_id' => $modelo->id,
            'motorista_id' => $motorista->id,
        ]);

        return $veiculo->load('modelo.marca');
    }

    public function listarMeusVeiculos(Usuario $usuarioAutenticado)
    {
        $motorista = Motorista::where('usuario_id', $usuarioAutenticado->id)->first();

        if (! $motorista) {
            throw new NegocioException('Motorista não encontrado.');
        }

        return Veiculo::with('modelo.marca')->where('motorista_id', $motorista->id)->get();
    }

    private function motoristaDoUsuario(Usuario $usuario): Motorista
    {
        $motorista = Motorista::where('usuario_id', $usuario->id)->first();

        if ($motorista) {
            return $motorista;
        }

        if ($usuario->tipo_usuario !== TipoUsuario::MOTORISTA) {
            throw new NegocioException('Apenas usuários do tipo MOTORISTA podem cadastrar veículos.');
        }

        return Motorista::create([
            'usuario_id' => $usuario->id,
            'cpf' => '00000000000',
        ]);
    }
}
