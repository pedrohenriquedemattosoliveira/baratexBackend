<?php

namespace Database\Seeders;

use App\Enums\TipoUsuario;
use App\Models\Endereco;
use App\Models\Motorista;
use App\Models\Oficina;
use App\Models\Servico;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dados de demonstração para testar o app de ponta a ponta — NÃO faz
 * parte do DatabaseSeeder; rode explicitamente, e só em ambiente de
 * desenvolvimento:
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Cria (senha de todos: "senha123"):
 *   - motorista@baratex.test  (motorista)
 *   - oficina1@baratex.test   (Auto Center Silva — Sé, São Paulo)
 *   - oficina2@baratex.test   (Speed Auto Elétrica — Pinheiros, São Paulo)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $senha = Hash::make('senha123');

        $motorista = Usuario::firstOrCreate(
            ['email' => 'motorista@baratex.test'],
            ['nome' => 'Motorista Demo', 'senha_hash' => $senha, 'telefone' => '11999990000', 'tipo_usuario' => TipoUsuario::MOTORISTA],
        );
        Motorista::firstOrCreate(['usuario_id' => $motorista->id], ['cpf' => '11144477735']);

        $this->oficina(
            email: 'oficina1@baratex.test',
            senha: $senha,
            nomeFantasia: 'Auto Center Silva',
            razaoSocial: 'Silva Manutenções Automotivas LTDA',
            cnpj: '12345678000195',
            endereco: ['Praça da Sé', '100', 'Sé', '01001000', -23.5505, -46.6333],
            servicos: [
                ['Troca de Óleo e Filtro', 'Óleo sintético 5W30 e filtro de óleo.', 220.00, 40],
                ['Troca de Pastilhas de Freio', 'Pastilhas dianteiras com revisão dos discos.', 180.00, 60],
                ['Alinhamento e Balanceamento', 'Alinhamento 3D e balanceamento das 4 rodas.', 150.00, 50],
            ],
        );

        $this->oficina(
            email: 'oficina2@baratex.test',
            senha: $senha,
            nomeFantasia: 'Speed Auto Elétrica',
            razaoSocial: 'Speed Serviços Elétricos Automotivos LTDA',
            cnpj: '98765432000110',
            endereco: ['Rua dos Pinheiros', '900', 'Pinheiros', '05422001', -23.5672, -46.6920],
            servicos: [
                ['Troca de Bateria 60Ah', 'Bateria nova com 18 meses de garantia.', 420.00, 30],
                ['Revisão de Ar Condicionado', 'Higienização, carga de gás e troca do filtro de cabine.', 260.00, 90],
            ],
        );
    }

    private function oficina(string $email, string $senha, string $nomeFantasia, string $razaoSocial, string $cnpj, array $endereco, array $servicos): void
    {
        if (Oficina::where('cnpj', $cnpj)->exists()) {
            return;
        }

        $usuario = Usuario::firstOrCreate(
            ['email' => $email],
            ['nome' => $nomeFantasia, 'senha_hash' => $senha, 'telefone' => '1133334444', 'tipo_usuario' => TipoUsuario::OFICINA],
        );

        [$logradouro, $numero, $bairro, $cep, $latitude, $longitude] = $endereco;

        $enderecoCriado = Endereco::create([
            'logradouro' => $logradouro,
            'numero' => $numero,
            'bairro' => $bairro,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => $cep,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $oficina = Oficina::create([
            'razao_social' => $razaoSocial,
            'nome_fantasia' => $nomeFantasia,
            'cnpj' => $cnpj,
            'usuario_id' => $usuario->id,
            'endereco_id' => $enderecoCriado->id,
        ]);

        foreach ($servicos as [$nome, $descricao, $preco, $minutos]) {
            Servico::create([
                'oficina_id' => $oficina->id,
                'nome' => $nome,
                'descricao' => $descricao,
                'preco_base' => $preco,
                'tempo_estimado_minutos' => $minutos,
            ]);
        }
    }
}
