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
        Schema::table('transactions', function (Blueprint $table) {
            // Adiciona a nova coluna 'type' como ENUM com valores e default
            $table->enum('type', ['TRANSFER', 'DEPOSIT', 'REVERSAL'])->default('DEPOSIT')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Remove a coluna 'type' se a migration for revertida
            $table->dropColumn('type');
        });
    }
};
