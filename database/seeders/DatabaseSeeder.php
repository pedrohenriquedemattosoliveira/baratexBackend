<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catálogo de marcas/modelos: necessário para o cadastro de
        // veículos do app. Idempotente — seguro rodar em qualquer deploy.
        $this->call(MarcaModeloSeeder::class);

        // Usuários/oficinas de demonstração ficam em DemoSeeder, que só
        // roda quando chamado explicitamente:
        //   php artisan db:seed --class=DemoSeeder
    }
}
