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
        Schema::dropIfExists('transaction_reversals');
        Schema::create('transaction_reversals', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Identificador único
            $table->uuid('original_transaction_id'); // ID da transação original que está sendo revertida
            $table->uuid('reversal_transaction_id')->nullable(); // ID da nova transação do tipo REVERSAL (pode ser null inicialmente até a transação ser criada)
            $table->uuid('reversed_by_user_id')->nullable(); // ID do usuário que solicitou a reversão (pode ser null se for processo automático)
            $table->text('reason'); // Motivo da reversão
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED'])->default('PENDING'); // Status da operação de reversão
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('original_transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('reversal_transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('reversed_by_user_id')->references('id')->on('users')->onDelete('set null'); // Ou 'restrict' dependendo da sua regra para exclusão de usuário
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_reversals');
    }
};
