<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Se você estiver usando MySQL e a coluna user_type for um ENUM,
        // precisará alterar a definição da coluna para adicionar o novo valor.
        // CUIDADO: Este comando pode ser destrutivo se houver dados inválidos
        // ou se o tipo de banco de dados não for MySQL.
        DB::statement("ALTER TABLE users CHANGE user_type user_type ENUM('COMMON', 'MERCHANT', 'ADMIN') NOT NULL DEFAULT 'COMMON'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ao reverter, remova 'ADMIN' do ENUM.
        // Considere o que fazer com usuários que já foram do tipo 'ADMIN'.
        // Se for simples, pode-se reverter para 'COMMON' ou 'MERCHANT'.
        // Para este exemplo, vamos reverter a definição do ENUM.
        DB::statement("ALTER TABLE users CHANGE user_type user_type ENUM('COMMON', 'MERCHANT') NOT NULL DEFAULT 'COMMON'");

        // Opcional: Se houver usuários 'ADMIN', você pode querer atualizá-los
        // para 'COMMON' ou 'MERCHANT' aqui antes de dropar o tipo no ENUM
        // para evitar erros de dados. Exemplo:
        // \App\Models\User::where('user_type', 'ADMIN')->update(['user_type' => \App\Enums\UserTypeEnum::COMMON]);
    }
};
