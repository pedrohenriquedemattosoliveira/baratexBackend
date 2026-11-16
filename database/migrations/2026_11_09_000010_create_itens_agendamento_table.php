<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_agendamento', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agendamento_id')->references('id')->on('agendamentos')->cascadeOnDelete();
            $table->foreignUuid('servico_id')->references('id')->on('servicos');
            $table->decimal('preco_praticado', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_agendamento');
    }
};
