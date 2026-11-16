<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garagem_virtual', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agendamento_id')->references('id')->on('agendamentos');
            $table->date('data_servico');
            $table->integer('quilometragem_registro');
            $table->text('descricao_servico');
            $table->boolean('validado_oficina')->default(false);
            $table->dateTime('data_validacao')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garagem_virtual');
    }
};
