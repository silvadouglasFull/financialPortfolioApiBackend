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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Identificador único (PK)

            // Usuário que envia
            $table->uuid('payer_id'); //
            $table->foreign('payer_id')->references('id')->on('users')->onDelete('restrict');

            // Usuário que recebe
            $table->uuid('payee_id'); //
            $table->foreign('payee_id')->references('id')->on('users')->onDelete('restrict');

            // Valor transferido
            $table->decimal('amount', 15, 2); //

            // Status da transação: PENDING, COMPLETED, FAILED, REVERSED
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REVERSED'])->default('PENDING'); //

            // Referência a uma transação revertida (útil para auditoria)
            $table->uuid('reverted_from')->nullable(); //
            // Chave estrangeira para a própria tabela, indicando a transação original revertida
            $table->foreign('reverted_from')->references('id')->on('transactions')->onDelete('set null');

            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
