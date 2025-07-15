<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garante que o UserSeeder já foi executado
        $this->call(UserSeeder::class);

        // Recupera todos os usuários.
        $users = User::all();

        // Pega o primeiro usuário como pagador.
        $payer = $users->first();

        // Se não houver usuários (o que não deve acontecer após UserSeeder), sai.
        if (!$payer) {
            return;
        }

        // Filtra os usuários para serem os recebedores, excluindo o próprio pagador.
        $payees = $users->reject(function ($user) use ($payer) {
            return $user->id === $payer->id;
        });

        // Limita o número de transações ao número de recebedores disponíveis, até um máximo de 5.
        $transactionsToCreate = min(5, $payees->count());

        $amount = 50.00;

        // Inicia uma transação de banco de dados para garantir a atomicidade das operações de saldo.
        DB::beginTransaction();

        try {
            foreach ($payees->take($transactionsToCreate) as $payee) {
                // Diminui o saldo do pagador
                $payer->balance -= $amount;
                $payer->save();

                // Aumenta o saldo do recebedor
                $payee->balance += $amount;
                $payee->save();

                // Cria a transação
                Transaction::create([
                    'payer_id' => $payer->id,
                    'payee_id' => $payee->id,
                    'amount' => $amount,
                    'status' => TransactionStatus::COMPLETED,
                    'type' => TransactionType::TRANSFER,
                    'reverted_from' => null,
                    'reason' => 'Transferência inicial via seeder',
                ]);
            }

            DB::commit(); // Confirma as alterações no banco de dados.
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte as alterações em caso de erro.
            throw $e; // Re-lança a exceção.
        }
    }
}
