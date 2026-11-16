<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('oficina_id')->references('id')->on('oficinas');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco_base', 10, 2);
            $table->integer('tempo_estimado_minutos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
