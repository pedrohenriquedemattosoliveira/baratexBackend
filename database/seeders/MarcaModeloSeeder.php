<?php

namespace Database\Seeders;

use App\Models\Marca;
use App\Models\ModeloVeiculo;
use Illuminate\Database\Seeder;

/**
 * Catálogo básico de marcas/modelos (os mais comuns no Brasil), usado
 * pelo cadastro de veículos do app. Idempotente: pode rodar quantas
 * vezes quiser sem duplicar registros.
 */
class MarcaModeloSeeder extends Seeder
{
    /**
     * marca => [modelo => [anoInicio, anoFim|null (ainda em linha)]]
     */
    private const CATALOGO = [
        'Chevrolet' => [
            'Onix' => [2012, null], 'Prisma' => [2006, 2019], 'Celta' => [2000, 2015],
            'Corsa' => [1994, 2012], 'Cruze' => [2011, 2024], 'S10' => [1995, null],
            'Tracker' => [2013, null], 'Spin' => [2012, null], 'Montana' => [2003, null],
        ],
        'Fiat' => [
            'Uno' => [1984, 2021], 'Palio' => [1996, 2017], 'Siena' => [1997, 2016],
            'Strada' => [1998, null], 'Argo' => [2017, null], 'Mobi' => [2016, null],
            'Toro' => [2016, null], 'Cronos' => [2018, null], 'Pulse' => [2021, null],
        ],
        'Volkswagen' => [
            'Gol' => [1980, 2023], 'Fox' => [2003, 2021], 'Polo' => [2002, null],
            'Voyage' => [1981, 2023], 'Saveiro' => [1982, null], 'Virtus' => [2018, null],
            'T-Cross' => [2019, null], 'Nivus' => [2020, null], 'Jetta' => [2006, null],
        ],
        'Ford' => [
            'Ka' => [1997, 2021], 'Fiesta' => [1996, 2019], 'Focus' => [2000, 2019],
            'EcoSport' => [2003, 2021], 'Ranger' => [1994, null],
        ],
        'Toyota' => [
            'Corolla' => [1992, null], 'Etios' => [2012, 2021], 'Yaris' => [2018, null],
            'Hilux' => [1992, null], 'Corolla Cross' => [2021, null],
        ],
        'Honda' => [
            'Civic' => [1992, null], 'Fit' => [2003, 2021], 'City' => [2009, null],
            'HR-V' => [2015, null], 'CR-V' => [1997, null],
        ],
        'Hyundai' => [
            'HB20' => [2012, null], 'HB20S' => [2013, null], 'Creta' => [2017, null],
            'Tucson' => [2004, null], 'i30' => [2009, 2017],
        ],
        'Renault' => [
            'Sandero' => [2007, 2023], 'Logan' => [2007, 2023], 'Kwid' => [2017, null],
            'Duster' => [2011, null], 'Clio' => [1996, 2016],
        ],
        'Nissan' => [
            'March' => [2011, 2020], 'Versa' => [2011, null], 'Kicks' => [2016, null],
            'Sentra' => [2004, null], 'Frontier' => [1998, null],
        ],
        'Jeep' => [
            'Renegade' => [2015, null], 'Compass' => [2016, null], 'Commander' => [2021, null],
        ],
        'Peugeot' => [
            '206' => [1999, 2010], '207' => [2008, 2015], '208' => [2013, null], '2008' => [2015, null],
        ],
        'Citroën' => [
            'C3' => [2003, null], 'C4 Cactus' => [2018, null],
        ],
        'Mitsubishi' => [
            'L200' => [1992, null], 'Pajero' => [1990, null], 'ASX' => [2010, null],
        ],
        'Kia' => [
            'Sportage' => [1996, null], 'Cerato' => [2005, null], 'Picanto' => [2006, 2021],
        ],
    ];

    public function run(): void
    {
        foreach (self::CATALOGO as $nomeMarca => $modelos) {
            $marca = Marca::firstOrCreate(['nome' => $nomeMarca]);

            foreach ($modelos as $nomeModelo => [$anoInicio, $anoFim]) {
                ModeloVeiculo::firstOrCreate(
                    ['marca_id' => $marca->id, 'nome_modelo' => $nomeModelo],
                    ['ano_inicio' => $anoInicio, 'ano_fim' => $anoFim],
                );
            }
        }
    }
}
