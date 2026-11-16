<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelos_veiculos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome_modelo');
            $table->integer('ano_inicio')->nullable();
            $table->integer('ano_fim')->nullable();
            $table->foreignUuid('marca_id')->references('id')->on('marcas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos_veiculos');
    }
};
