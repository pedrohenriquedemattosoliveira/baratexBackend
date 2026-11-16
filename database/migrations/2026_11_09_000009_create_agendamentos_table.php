<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('oficina_id')->references('id')->on('oficinas');
            $table->foreignUuid('veiculo_id')->references('id')->on('tb_veiculo');
            $table->dateTime('data_hora');
            $table->string('status')->default('PENDENTE');
            $table->decimal('valor_total', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
