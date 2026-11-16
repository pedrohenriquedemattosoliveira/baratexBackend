<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha_hash');
            $table->string('telefone')->nullable();
            $table->string('tipo_usuario'); // MOTORISTA | OFICINA | ADMIN
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
