<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Atualizar a coluna 'status' para incluir 'DENIED'
            // MySQL ENUMs exigem uma alteração de coluna.
            // A sintaxe pode variar ligeiramente dependendo do DB, mas para MySQL é:
            DB::statement("ALTER TABLE transactions CHANGE status status ENUM('PENDING', 'COMPLETED', 'FAILED', 'DENIED') NOT NULL");
            // 2. Adicionar a nova coluna 'reason'
            $table->string('reason')->nullable()->after('type'); // Adicionar após 'type'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Reverter a coluna 'status' removendo 'DENIED'
            // Em um rollback, você pode querer reverter para os estados anteriores.
            // Se houver transações com status DENIED, isso falhará.
            // Para simplicidade, vamos para o estado original menos DENIED.
            DB::statement("ALTER TABLE transactions CHANGE status status ENUM('PENDING', 'COMPLETED', 'FAILED') NOT NULL");

            // 2. Remover a coluna 'reason'
            $table->dropColumn('reason');
        });
    }
};
