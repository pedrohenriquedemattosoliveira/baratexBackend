<?php

namespace Tests\Feature;

use App\Models\ModeloVeiculo;
use Database\Seeders\DemoSeeder;
use Database\Seeders\MarcaModeloSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Percorre a API do jeito que o app mobile a usa: auto-cadastro de
 * prestador e de motorista, serviço criado pela própria oficina,
 * agendamento, chat, mudança de status, garagem virtual e avaliação.
 */
class AppMobileFlowTest extends TestCase
{
    use RefreshDatabase;

    private function dadosOficina(array $sobrescrever = []): array
    {
        return array_replace_recursive([
            'nome' => 'Dona da Oficina',
            'email' => 'prestador@example.com',
            'senha' => 'senha123',
            'telefone' => '11988887777',
            'tipoUsuario' => 'OFICINA',
            'oficina' => [
                'razaoSocial' => 'Oficina App LTDA',
                'nomeFantasia' => 'Oficina App',
                'cnpj' => '12.345.678/0001-99',
                'endereco' => [
                    'logradouro' => 'Rua das Flores',
                    'numero' => '100',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'estado' => 'sp',
                    'cep' => '01000000',
                    'latitude' => -23.55,
                    'longitude' => -46.63,
                ],
            ],
        ], $sobrescrever);
    }

    private function login(string $email): string
    {
        return $this->postJson('/api/auth/login', ['email' => $email, 'senha' => 'senha123'])
            ->assertStatus(200)
            ->json('token');
    }

    public function test_fluxo_do_app_de_ponta_a_ponta(): void
    {
        $this->seed(MarcaModeloSeeder::class);

        // Prestador se cadastra sozinho (sem JWT) e já sai com a oficina criada.
        $this->postJson('/api/auth/register', $this->dadosOficina())->assertStatus(201);
        $tokenOficina = $this->login('prestador@example.com');

        $oficina = $this->withToken($tokenOficina)->getJson('/api/oficinas/me')
            ->assertStatus(200)
            ->assertJsonPath('nomeFantasia', 'Oficina App')
            ->assertJsonPath('cnpj', '12345678000199')
            ->assertJsonPath('estado', 'SP')
            ->assertJsonPath('totalAvaliacoes', 0);
        $oficinaId = $oficina->json('id');

        // Cria um serviço sem informar oficinaId: vale a oficina do token.
        $servicoId = $this->withToken($tokenOficina)->postJson('/api/servicos', [
            'nome' => 'Troca de óleo',
            'preco' => 150,
            'tempoEstimadoMinutos' => 45,
        ])->assertStatus(201)->assertJsonPath('oficinaId', $oficinaId)->json('id');

        // Motorista se cadastra, adiciona um veículo e agenda.
        $this->postJson('/api/auth/register', [
            'nome' => 'Motorista App',
            'email' => 'motorista.app@example.com',
            'senha' => 'senha123',
            'cpf' => '111.444.777-35',
            'tipoUsuario' => 'MOTORISTA',
        ])->assertStatus(201);
        $tokenMotorista = $this->login('motorista.app@example.com');

        $modelo = ModeloVeiculo::where('nome_modelo', 'Onix')->firstOrFail();
        $veiculoId = $this->withToken($tokenMotorista)->postJson('/api/veiculos', [
            'placa' => 'abc1d23',
            'modeloId' => $modelo->id,
            'ano' => 2020,
        ])->assertStatus(201)->json('id');

        $agendamento = $this->withToken($tokenMotorista)->postJson('/api/agendamentos', [
            'oficinaId' => $oficinaId,
            'veiculoId' => $veiculoId,
            'dataHora' => '2030-01-15T10:00:00-03:00',
            'itens' => [['servicoId' => $servicoId]],
        ])->assertStatus(200)
            ->assertJsonPath('status', 'PENDENTE')
            ->assertJsonPath('dataHora', '2030-01-15T13:00:00+00:00')
            ->assertJsonPath('itens.0.nome', 'Troca de óleo')
            ->assertJsonPath('oficina.nomeFantasia', 'Oficina App')
            ->assertJsonPath('veiculo.motoristaNome', 'Motorista App')
            ->assertJsonPath('avaliacao', null)
            ->assertJsonPath('registradoNaGaragem', false);
        $agendamentoId = $agendamento->json('id');

        // Os dois lados enxergam o agendamento.
        $this->withToken($tokenMotorista)->getJson('/api/agendamentos/minhas')->assertStatus(200)->assertJsonCount(1);
        $this->withToken($tokenOficina)->getJson('/api/agendamentos/minha-oficina')->assertStatus(200)->assertJsonCount(1);

        // Chat: o remetente é sempre o usuário do token.
        $this->withToken($tokenMotorista)->postJson('/api/chat', [
            'agendamentoId' => $agendamentoId,
            'conteudo' => 'Posso chegar 10 minutos antes?',
        ])->assertStatus(200)->assertJsonPath('remetenteNome', 'Motorista App');

        $this->withToken($tokenOficina)->getJson("/api/chat/agendamento/{$agendamentoId}")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.conteudo', 'Posso chegar 10 minutos antes?');

        // A oficina conduz o status (corpo JSON, como o app envia).
        foreach (['CONFIRMADO', 'EM_ANDAMENTO', 'CONCLUIDO'] as $status) {
            $this->withToken($tokenOficina)
                ->patchJson("/api/agendamentos/{$agendamentoId}/status", ['status' => $status])
                ->assertStatus(200)
                ->assertJsonPath('status', $status);
        }

        // Registro na Garagem Virtual e consulta pelo motorista.
        $this->withToken($tokenOficina)->postJson('/api/garagem-virtual', [
            'agendamentoId' => $agendamentoId,
            'dataServico' => '2030-01-15',
            'quilometragemRegistro' => 45000,
            'descricaoServico' => 'Troca de óleo 5W30 e filtro.',
            'validadoOficina' => true,
        ])->assertStatus(200);

        $this->withToken($tokenMotorista)->getJson("/api/garagem-virtual/veiculo/{$veiculoId}")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.oficina.nomeFantasia', 'Oficina App')
            ->assertJsonPath('0.oficina.cnpj', '12345678000199')
            ->assertJsonPath('0.servicos.0', 'Troca de óleo')
            ->assertJsonPath('0.validadoOficina', true);

        // Avaliação: uma só por agendamento, e ela aparece na oficina.
        $avaliacao = ['agendamentoId' => $agendamentoId, 'nota' => 4, 'comentario' => 'Bom atendimento.'];
        $this->withToken($tokenMotorista)->postJson('/api/avaliacoes', $avaliacao)->assertStatus(200);
        $this->withToken($tokenMotorista)->postJson('/api/avaliacoes', $avaliacao)
            ->assertStatus(400)
            ->assertJsonPath('mensagem', 'Este agendamento já foi avaliado.');

        $this->withToken($tokenMotorista)->getJson("/api/agendamentos/{$agendamentoId}")
            ->assertStatus(200)
            ->assertJsonPath('avaliacao.nota', 4)
            ->assertJsonPath('registradoNaGaragem', true);

        $this->getJson("/api/oficinas/{$oficinaId}")
            ->assertStatus(200)
            ->assertJsonPath('notaMedia', 4)
            ->assertJsonPath('totalAvaliacoes', 1);
    }

    public function test_cadastro_de_oficina_valida_dados_e_duplicidade(): void
    {
        // Bloco "oficina" incompleto => 422 com o campo apontado.
        $this->postJson('/api/auth/register', $this->dadosOficina(['oficina' => ['cnpj' => '']]))
            ->assertStatus(422)
            ->assertJsonStructure(['errosValidacao' => ['oficina.cnpj']]);
        $this->assertDatabaseMissing('usuarios', ['email' => 'prestador@example.com']);

        $this->postJson('/api/auth/register', $this->dadosOficina())->assertStatus(201);

        // Mesmo CNPJ com outro e-mail => 400 e nada fica pela metade.
        $this->postJson('/api/auth/register', $this->dadosOficina(['email' => 'outro@example.com']))
            ->assertStatus(400)
            ->assertJsonPath('mensagem', 'CNPJ já cadastrado.');
        $this->assertDatabaseMissing('usuarios', ['email' => 'outro@example.com']);
    }

    public function test_agendamento_recusa_servico_de_outra_oficina(): void
    {
        $this->seed([MarcaModeloSeeder::class, DemoSeeder::class]);

        $token = $this->login('motorista@baratex.test');
        $oficinas = $this->getJson('/api/oficinas')->assertStatus(200)->assertJsonCount(2)->json();
        $servicoDaSegunda = $this->getJson("/api/servicos/oficina/{$oficinas[1]['id']}")->json('0.id');

        $veiculoId = $this->withToken($token)->postJson('/api/veiculos', [
            'placa' => 'XYZ9876',
            'modeloId' => ModeloVeiculo::firstOrFail()->id,
            'ano' => 2019,
        ])->assertStatus(201)->json('id');

        $this->withToken($token)->postJson('/api/agendamentos', [
            'oficinaId' => $oficinas[0]['id'],
            'veiculoId' => $veiculoId,
            'dataHora' => '2030-01-15T10:00:00Z',
            'itens' => [['servicoId' => $servicoDaSegunda]],
        ])->assertStatus(400);
    }

    public function test_id_que_nao_e_uuid_responde_404(): void
    {
        $this->getJson('/api/oficinas/abc')->assertStatus(404);
        $this->getJson('/api/servicos/oficina/123')->assertStatus(404);
    }

    public function test_seeder_de_marcas_e_idempotente(): void
    {
        $this->seed(MarcaModeloSeeder::class);
        $total = ModeloVeiculo::count();
        $this->seed(MarcaModeloSeeder::class);

        $this->assertGreaterThan(50, $total);
        $this->assertSame($total, ModeloVeiculo::count());
    }
}
