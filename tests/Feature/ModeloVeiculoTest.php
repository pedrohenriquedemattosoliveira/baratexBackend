<?php

namespace Tests\Feature;

use App\Models\Marca;
use App\Models\ModeloVeiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModeloVeiculoTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_marcas_e_modelos_e_anos(): void
    {
        $fiat = Marca::create(['nome' => 'Fiat']);
        Marca::create(['nome' => 'Chevrolet']);

        $uno = ModeloVeiculo::create([
            'nome_modelo' => 'Uno',
            'ano_inicio' => 2000,
            'ano_fim' => 2003,
            'marca_id' => $fiat->id,
        ]);

        $this->getJson('/api/modelos-veiculos/marcas?q=fi')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.nome', 'Fiat');

        $this->getJson("/api/modelos-veiculos/modelos?marcaId={$fiat->id}")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.nomeModelo', 'Uno');

        $this->getJson("/api/modelos-veiculos/anos?modeloId={$uno->id}")
            ->assertStatus(200)
            ->assertExactJson([2003, 2002, 2001, 2000]);
    }

    public function test_anos_retorna_404_para_modelo_inexistente(): void
    {
        $this->getJson('/api/modelos-veiculos/anos?modeloId='.Str::uuid())
            ->assertStatus(404);
    }
}
