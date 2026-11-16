<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_e_autentica_um_motorista(): void
    {
        $this->postJson('/api/auth/register', [
            'nome' => 'João da Silva',
            'email' => 'joao@example.com',
            'senha' => 'senha123',
            'telefone' => '11999990000',
            'cpf' => '123.456.789-00',
            'tipoUsuario' => 'MOTORISTA',
        ])->assertStatus(201);

        $this->assertDatabaseHas('usuarios', ['email' => 'joao@example.com']);
        $this->assertDatabaseHas('motoristas', ['cpf' => '12345678900']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'joao@example.com',
            'senha' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'tipo', 'usuarioId', 'nome', 'email', 'tipoUsuario'])
            ->assertJsonPath('tipoUsuario', 'MOTORISTA');
    }

    public function test_recusa_credenciais_invalidas(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'inexistente@example.com',
            'senha' => 'qualquer',
        ]);

        $response->assertStatus(400)->assertJsonPath('erro', 'Regra de Negócio Violada');
    }

    public function test_exige_cpf_para_cadastrar_motorista(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nome' => 'Sem CPF',
            'email' => 'semcpf@example.com',
            'senha' => 'senha123',
            'tipoUsuario' => 'MOTORISTA',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('usuarios', ['email' => 'semcpf@example.com']);
    }

    public function test_bloqueia_acesso_sem_token(): void
    {
        $this->getJson('/api/veiculos')->assertStatus(401);
    }
}
