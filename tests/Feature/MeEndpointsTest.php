<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeEndpointsTest extends TestCase
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

    public function test_motoristas_me_e_agendamentos_minhas(): void
    {
        $token = $this->autenticar([
            'nome' => 'Motorista Teste',
            'email' => 'motorista.me@example.com',
            'senha' => 'senha123',
            'cpf' => '11144477735',
            'tipoUsuario' => 'MOTORISTA',
        ]);

        $this->withToken($token)->getJson('/api/motoristas/me')
            ->assertStatus(200)
            ->assertJsonPath('email', 'motorista.me@example.com');

        $this->withToken($token)->getJson('/api/agendamentos/minhas')
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_oficinas_me_e_agendamentos_minha_oficina(): void
    {
        // Bootstrap: precisa de um usuário autenticado para criar a
        // oficina (POST /api/oficinas exige JWT, fiel à API original).
        $tokenBootstrap = $this->autenticar([
            'nome' => 'Admin Bootstrap',
            'email' => 'admin.bootstrap@example.com',
            'senha' => 'senha123',
            'tipoUsuario' => 'ADMIN',
        ]);

        $this->withToken($tokenBootstrap)->postJson('/api/oficinas', [
            'razaoSocial' => 'Oficina Me LTDA',
            'nomeFantasia' => 'Oficina Me',
            'cnpj' => '12345678000199',
            'usuario' => [
                'nome' => 'Dona da Oficina',
                'email' => 'oficina.me@example.com',
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
            ],
        ])->assertStatus(201);

        // Agora loga COMO a própria oficina criada, para testar /me.
        $tokenOficina = $this->postJson('/api/auth/login', [
            'email' => 'oficina.me@example.com',
            'senha' => 'senha123',
        ])->assertStatus(200)->json('token');

        $this->withToken($tokenOficina)->getJson('/api/oficinas/me')
            ->assertStatus(200)
            ->assertJsonPath('email', 'oficina.me@example.com')
            ->assertJsonPath('nomeFantasia', 'Oficina Me');

        $this->withToken($tokenOficina)->getJson('/api/agendamentos/minha-oficina')
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_me_retorna_erro_de_negocio_quando_nao_ha_cadastro_correspondente(): void
    {
        $token = $this->autenticar([
            'nome' => 'Usuario Sem Perfil',
            'email' => 'semperfil@example.com',
            'senha' => 'senha123',
            'tipoUsuario' => 'ADMIN',
        ]);

        $this->withToken($token)->getJson('/api/motoristas/me')->assertStatus(400);
        $this->withToken($token)->getJson('/api/oficinas/me')->assertStatus(400);
    }
}
