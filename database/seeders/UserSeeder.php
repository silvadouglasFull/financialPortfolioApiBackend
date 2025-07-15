<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // Importar Str para UUIDs

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Usuário Administrador
        User::create([
            'id' => (string) Str::uuid(), // Garantir UUID para o ID
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@123'), // Senha forte para o admin
            'document' => '12345678900', // CPF fixo para o admin, ou use generateUniqueCpf()
            'balance' => 0.00,
            'user_type' => UserTypeEnum::ADMIN,
            'google_id' => null,
        ]);

        // 2. 5 Usuários aleatórios (COMMON ou MERCHANT) com saldo de 10.000,00
        // A Factory cuidará de gerar name, email, password, document, id e google_id
        User::factory()->count(5)->create([
            'balance' => 10000.00,
            // A factory já define 'user_type' como COMMON. Se quiser MERCHANT, pode sobrescrever:
            'user_type' => UserTypeEnum::COMMON,
        ]);

        // O método generateUniqueCpf() não é mais estritamente necessário para os usuários
        // gerados pela factory, pois a factory pode gerar documentos aleatórios.
        // Se você precisa que os documentos da factory sejam CPFs válidos (mesmo que fictícios),
        // precisaria ajustar a UserFactory para gerar CPFs no formato correto.
        // Para o admin, mantive um CPF fixo por ser um usuário específico.
    }
}
