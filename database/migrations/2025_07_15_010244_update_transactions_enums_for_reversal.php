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
            // Atualizar a coluna 'status' para incluir 'REVERSED'
            DB::statement("ALTER TABLE transactions CHANGE status status ENUM('PENDING', 'COMPLETED', 'FAILED', 'DENIED', 'REVERSED') NOT NULL");

            // Atualizar a coluna 'type' para incluir 'REVERSAL'
            DB::statement("ALTER TABLE transactions CHANGE type type ENUM('TRANSFER', 'DEPOSIT', 'REVERSAL') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Reverter a coluna 'status' removendo 'REVERSED'
            DB::statement("ALTER TABLE transactions CHANGE status status ENUM('PENDING', 'COMPLETED', 'FAILED', 'DENIED') NOT NULL");

            // Reverter a coluna 'type' removendo 'REVERSAL'
            DB::statement("ALTER TABLE transactions CHANGE type type ENUM('TRANSFER', 'DEPOSIT') NOT NULL");
        });
    }
};
