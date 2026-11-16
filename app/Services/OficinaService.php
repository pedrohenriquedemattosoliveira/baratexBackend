<?php

namespace App\Services;

use App\Enums\TipoUsuario;
use App\Exceptions\NegocioException;
use App\Models\Endereco;
use App\Models\Oficina;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OficinaService
{
    public function listarTodas()
    {
        return Oficina::with(['usuario', 'endereco'])->get();
    }

    public function buscarPorId(string $id): Oficina
    {
        $oficina = Oficina::with(['usuario', 'endereco'])->find($id);

        if (! $oficina) {
            throw new NegocioException("Oficina não encontrada com o ID: {$id}");
        }

        return $oficina;
    }

    public function salvar(array $dados): Oficina
    {
        return DB::transaction(function () use ($dados) {
            $usuario = Usuario::create([
                'nome' => $dados['usuario']['nome'],
                'email' => $dados['usuario']['email'],
                'senha_hash' => Hash::make($dados['usuario']['senhaHash']),
                'telefone' => $dados['usuario']['telefone'] ?? null,
                'tipo_usuario' => TipoUsuario::OFICINA,
            ]);

            $endereco = Endereco::create([
                'logradouro' => $dados['endereco']['logradouro'],
                'numero' => $dados['endereco']['numero'],
                'bairro' => $dados['endereco']['bairro'],
                'cidade' => $dados['endereco']['cidade'],
                'estado' => $dados['endereco']['estado'],
                'cep' => $dados['endereco']['cep'],
                'latitude' => $dados['endereco']['latitude'] ?? null,
                'longitude' => $dados['endereco']['longitude'] ?? null,
            ]);

            $oficina = Oficina::create([
                'razao_social' => $dados['razaoSocial'],
                'nome_fantasia' => $dados['nomeFantasia'],
                'cnpj' => $dados['cnpj'],
                'usuario_id' => $usuario->id,
                'endereco_id' => $endereco->id,
            ]);

            return $oficina->load(['usuario', 'endereco']);
        });
    }

    /**
     * Equivalente a OficinaRepository#buscarProximosNative, mas sem
     * depender de uma extensão espacial (PostGIS): calcula a distância
     * em km pela fórmula de Haversine diretamente em SQL, usando as
     * colunas planas latitude/longitude — funciona em MySQL/MariaDB
     * puro (XAMPP incluso). Não suportado em SQLite (sem funções
     * trigonométricas nativas), usado apenas nos testes automatizados.
     */
    public function buscarProximos(float $latitude, float $longitude, float $raioKm)
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'pgsql'], true)) {
            throw new NegocioException('A busca de oficinas próximas não é suportada neste banco de dados.');
        }

        $ids = DB::table('oficinas as o')
            ->join('enderecos as e', 'o.endereco_id', '=', 'e.id')
            ->whereNotNull('e.latitude')
            ->whereNotNull('e.longitude')
            ->whereRaw(
                '6371 * ACOS(LEAST(1, COS(RADIANS(?)) * COS(RADIANS(e.latitude)) * COS(RADIANS(e.longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(e.latitude)))) <= ?',
                [$latitude, $longitude, $latitude, $raioKm]
            )
            ->pluck('o.id');

        return Oficina::with(['usuario', 'endereco'])->whereIn('id', $ids)->get();
    }

    /**
     * Resolve a Oficina do usuário autenticado — usado por
     * GET /api/oficinas/me, para o app do prestador não precisar
     * conhecer o UUID da oficina de antemão.
     */
    public function buscarPorUsuario(Usuario $usuario): Oficina
    {
        $oficina = Oficina::with(['usuario', 'endereco'])->where('usuario_id', $usuario->id)->first();

        if (! $oficina) {
            throw new NegocioException('Nenhum cadastro de oficina encontrado para este usuário.');
        }

        return $oficina;
    }
}
