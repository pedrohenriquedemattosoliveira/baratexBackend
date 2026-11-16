<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensagens_chat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agendamento_id')->references('id')->on('agendamentos');
            $table->foreignUuid('remetente_id')->references('id')->on('usuarios');
            $table->text('conteudo');
            $table->dateTime('enviado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensagens_chat');
    }
};
