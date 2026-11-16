<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('razao_social');
            $table->string('nome_fantasia');
            $table->string('cnpj')->unique();
            $table->foreignUuid('usuario_id')->nullable()->references('id')->on('usuarios');
            $table->foreignUuid('endereco_id')->nullable()->references('id')->on('enderecos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficinas');
    }
};
