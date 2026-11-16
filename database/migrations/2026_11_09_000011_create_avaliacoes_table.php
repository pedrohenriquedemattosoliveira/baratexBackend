<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agendamento_id')->unique()->references('id')->on('agendamentos');
            $table->integer('nota');
            $table->text('comentario')->nullable();
            $table->dateTime('criado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
