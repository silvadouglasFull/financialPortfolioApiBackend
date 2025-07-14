<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a tabela 'users' já existe. Se sim, a deleta para recriar.
        // Isso é útil em ambientes de desenvolvimento para garantir um estado limpo.
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            // Coluna 'id' como UUID (PK)
            $table->uuid('id')->primary()->comment('Identificador único do usuário (UUID)');

            // Coluna 'name' (string)
            $table->string('name')->comment('Nome completo do usuário');

            // Coluna 'email' (string, único, usado para login)
            $table->string('email')->unique()->comment('Endereço de e-mail único, usado para login');

            // Coluna 'password' (string, hash bcrypt)
            $table->string('password')->comment('Senha segura (bcrypt)');

            // Coluna 'document' (string, CPF/CNPJ)
            $table->string('document')->unique()->comment('Documento de identificação (CPF/CNPJ) do usuário');

            // Coluna 'balance' (decimal 15,2, default 0)
            $table->decimal('balance', 15, 2)->default(0)->comment('Saldo financeiro atual do usuário');

            // Coluna 'user_type' (enum)
            $table->enum('user_type', ['COMMON', 'MERCHANT'])->comment('Tipo de usuário (COMMON: usuário comum, MERCHANT: lojista/comerciante)');

            // Timestamps 'created_at' e 'updated_at'
            $table->timestamps();

            // Adiciona um comentário à tabela como um todo
            $table->comment('Tabela de usuários do sistema de carteira financeira');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
