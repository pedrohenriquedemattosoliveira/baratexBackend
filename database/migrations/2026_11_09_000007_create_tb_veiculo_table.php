<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nome de tabela preservado da API original (Veiculo.java usa
        // @Table(name = "tb_veiculo")).
        Schema::create('tb_veiculo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('placa', 10);
            $table->integer('ano');
            $table->string('cor')->nullable();
            $table->foreignUuid('modelo_id')->references('id')->on('modelos_veiculos');
            $table->foreignUuid('motorista_id')->references('id')->on('motoristas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_veiculo');
    }
};
