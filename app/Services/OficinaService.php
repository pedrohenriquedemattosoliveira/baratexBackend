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
    /**
     * Relações + agregados de avaliação (nota média / total) usados pelo
     * OficinaResource em todas as listagens.
     */
    private function consulta()
    {
        return Oficina::with(['usuario', 'endereco'])
            ->withAvg('avaliacoes', 'nota')
            ->withCount('avaliacoes');
    }

    public function listarTodas()
    {
        return $this->consulta()->orderBy('nome_fantasia')->get();
    }

    public function buscarPorId(string $id): Oficina
    {
        $oficina = $this->consulta()->find($id);

        if (! $oficina) {
            throw new NegocioException("Oficina não encontrada com o ID: {$id}");
        }

        return $oficina;
    }

    public function salvar(array $dados): Oficina
    {
        if (Usuario::whereRaw('LOWER(email) = LOWER(?)', [$dados['usuario']['email']])->exists()) {
            throw new NegocioException('E-mail já cadastrado.');
        }

        return DB::transaction(function () use ($dados) {
            $usuario = Usuario::create([
                'nome' => $dados['usuario']['nome'],
                'email' => $dados['usuario']['email'],
                'senha_hash' => Hash::make($dados['usuario']['senhaHash']),
                'telefone' => $dados['usuario']['telefone'] ?? null,
                'tipo_usuario' => TipoUsuario::OFICINA,
            ]);

            return $this->criarParaUsuario($usuario, $dados);
        });
    }

    /**
     * Cria a Oficina (+ Endereco) de um usuário já existente. Usado por
     * salvar() e pelo auto-cadastro de prestador em
     * AuthService::registrar() (POST /api/auth/register com
     * tipoUsuario=OFICINA).
     *
     * @param  array  $dados  razaoSocial, nomeFantasia, cnpj, endereco{...}
     */
    public function criarParaUsuario(Usuario $usuario, array $dados): Oficina
    {
        $cnpj = preg_replace('/\D/', '', (string) $dados['cnpj']);

        if (Oficina::where('cnpj', $cnpj)->exists()) {
            throw new NegocioException('CNPJ já cadastrado.');
        }

        $endereco = Endereco::create([
            'logradouro' => $dados['endereco']['logradouro'],
            'numero' => $dados['endereco']['numero'],
            'complemento' => $dados['endereco']['complemento'] ?? null,
            'bairro' => $dados['endereco']['bairro'],
            'cidade' => $dados['endereco']['cidade'],
            'estado' => mb_strtoupper($dados['endereco']['estado']),
            'cep' => $dados['endereco']['cep'],
            'latitude' => $dados['endereco']['latitude'] ?? null,
            'longitude' => $dados['endereco']['longitude'] ?? null,
        ]);

        $oficina = Oficina::create([
            'razao_social' => $dados['razaoSocial'],
            'nome_fantasia' => $dados['nomeFantasia'],
            'cnpj' => $cnpj,
            'usuario_id' => $usuario->id,
            'endereco_id' => $endereco->id,
        ]);

        return $oficina->load(['usuario', 'endereco']);
    }

    /**
     * Equivalente a OficinaRepository#buscarProximosNative: oficinas a até
     * $raioKm do ponto informado, da mais próxima para a mais distante,
     * via PostGIS (ST_DWithin + ST_Distance sobre a coluna geography
     * "enderecos.localizacao", indexada com GiST). Cada oficina devolvida
     * leva o atributo "distancia_km".
     *
     * Exige PostgreSQL + PostGIS — não suportado no SQLite usado pela
     * suíte de testes.
     */
    public function buscarProximos(float $latitude, float $longitude, float $raioKm)
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new NegocioException('A busca de oficinas próximas exige PostgreSQL com PostGIS.');
        }

        // PostGIS usa a ordem (longitude, latitude) em ST_MakePoint.
        $ponto = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography';

        $distancias = DB::table('oficinas as o')
            ->join('enderecos as e', 'o.endereco_id', '=', 'e.id')
            ->selectRaw("o.id, ST_Distance(e.localizacao, {$ponto}) / 1000 as distancia_km", [$longitude, $latitude])
            ->whereRaw("ST_DWithin(e.localizacao, {$ponto}, ?)", [$longitude, $latitude, $raioKm * 1000])
            ->orderBy('distancia_km')
            ->pluck('distancia_km', 'o.id');

        return $this->consulta()
            ->whereIn('id', $distancias->keys())
            ->get()
            ->each(fn (Oficina $oficina) => $oficina->setAttribute('distancia_km', round((float) $distancias[$oficina->id], 2)))
            ->sortBy('distancia_km')
            ->values();
    }

    /**
     * Resolve a Oficina do usuário autenticado — usado por
     * GET /api/oficinas/me, para o app do prestador não precisar
     * conhecer o UUID da oficina de antemão.
     */
    public function buscarPorUsuario(Usuario $usuario): Oficina
    {
        $oficina = $this->consulta()->where('usuario_id', $usuario->id)->first();

        if (! $oficina) {
            throw new NegocioException('Nenhum cadastro de oficina encontrado para este usuário.');
        }

        return $oficina;
    }
}
