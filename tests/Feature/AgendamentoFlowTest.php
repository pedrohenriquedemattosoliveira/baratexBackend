<?php

namespace Tests\Feature;

use App\Models\Marca;
use App\Models\ModeloVeiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendamentoFlowTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar(array $dadosRegistro): string
    {
        $this->postJson('/api/auth/register', $dadosRegistro)->assertStatus(201);

        $login = $this->postJson('/api/auth/login', [
            'email' => $dadosRegistro['email'],
            'senha' => $dadosRegistro['senha'],
        ])->assertStatus(200);

        return $login->json('token');
    }

    public function test_fluxo_completo_de_agendamento(): void
    {
        // 1. Autentica um usuário qualquer para poder criar a oficina
        // (POST /api/oficinas exige JWT, assim como na API original).
        $token = $this->autenticar([
            'nome' => 'Admin',
            'email' => 'admin@example.com',
            'senha' => 'senha123',
            'tipoUsuario' => 'ADMIN',
        ]);

        $respostaOficina = $this->withToken($token)->postJson('/api/oficinas', [
            'razaoSocial' => 'Oficina Central LTDA',
            'nomeFantasia' => 'Oficina Central',
            'cnpj' => '12345678000199',
            'usuario' => [
                'nome' => 'Dona da Oficina',
                'email' => 'oficina@example.com',
                'senhaHash' => 'senha123',
                'telefone' => '11988887777',
            ],
            'endereco' => [
                'logradouro' => 'Rua das Flores',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01000000',
                'latitude' => -23.55,
                'longitude' => -46.63,
            ],
        ])->assertStatus(201);

        $oficinaId = $respostaOficina->json('id');

        $respostaServico = $this->withToken($token)->postJson('/api/servicos', [
            'nome' => 'Troca de óleo',
            'descricao' => 'Troca de óleo e filtro',
            'preco' => 150.00,
            'tempoEstimadoMinutos' => 60,
            'oficinaId' => $oficinaId,
        ])->assertStatus(201);

        $servicoId = $respostaServico->json('id');

        // 2. Cadastra marca/modelo (normalmente via seeder) e autentica o motorista
        $marca = Marca::create(['nome' => 'Fiat']);
        $modelo = ModeloVeiculo::create([
            'nome_modelo' => 'Uno',
            'ano_inicio' => 2000,
            'ano_fim' => 2020,
            'marca_id' => $marca->id,
        ]);

        $tokenMotorista = $this->autenticar([
            'nome' => 'Motorista Teste',
            'email' => 'motorista@example.com',
            'senha' => 'senha123',
            'cpf' => '98765432100',
            'tipoUsuario' => 'MOTORISTA',
        ]);

        $respostaVeiculo = $this->withToken($tokenMotorista)->postJson('/api/veiculos', [
            'placa' => 'abc1234',
            'modeloId' => $modelo->id,
            'ano' => 2015,
            'cor' => 'Prata',
        ])->assertStatus(201);

        $respostaVeiculo->assertJsonPath('placa', 'ABC1234')
            ->assertJsonPath('marca', 'Fiat')
            ->assertJsonPath('modelo', 'Uno');

        $veiculoId = $respostaVeiculo->json('id');

        $this->withToken($tokenMotorista)->getJson('/api/veiculos')
            ->assertStatus(200)
            ->assertJsonCount(1);

        // 3. Cria o agendamento e confirma o cálculo do valor total
        $respostaAgendamento = $this->withToken($tokenMotorista)->postJson('/api/agendamentos', [
            'oficinaId' => $oficinaId,
            'veiculoId' => $veiculoId,
            'dataHora' => now()->addDay()->toIso8601String(),
            'itens' => [
                ['servicoId' => $servicoId],
            ],
        ])->assertStatus(200);

        $respostaAgendamento->assertJsonPath('valorTotal', '150.00');
        $agendamentoId = $respostaAgendamento->json('id');

        // 4. Atualiza o status para CONCLUIDO
        $this->withToken($tokenMotorista)
            ->patchJson("/api/agendamentos/{$agendamentoId}/status?status=CONCLUIDO")
            ->assertStatus(200)
            ->assertJsonPath('status', 'CONCLUIDO');

        // 5. Registra uma avaliação
        $this->withToken($tokenMotorista)->postJson('/api/avaliacoes', [
            'agendamentoId' => $agendamentoId,
            'nota' => 5,
            'comentario' => 'Ótimo atendimento!',
        ])->assertStatus(200);

        $this->assertDatabaseHas('avaliacoes', ['agendamento_id' => $agendamentoId, 'nota' => 5]);
    }
}
