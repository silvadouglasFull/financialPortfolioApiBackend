<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $payer = User::factory()->create(['user_type' => \App\Enums\UserTypeEnum::COMMON]);
        $payee = User::factory()->create(['user_type' => \App\Enums\UserTypeEnum::COMMON]);

        return [
            'id' => (string) Str::uuid(),
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => TransactionStatus::COMPLETED,
            'type' => TransactionType::TRANSFER,
            'reverted_from' => null,
            'reason' => null,
        ];
    }

    public function deposit(): static
    {
        return $this->state(fn(array $attributes) => [
            'payer_id' => null,
            'payee_id' => User::factory()->create(['user_type' => \App\Enums\UserTypeEnum::COMMON])->id,
            'type' => TransactionType::DEPOSIT,
        ]);
    }

    public function withStatus(TransactionStatus $status): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => $status,
        ]);
    }

    public function withReason(string $reason): static
    {
        return $this->state(fn(array $attributes) => [
            'reason' => $reason,
        ]);
    }

    public function reversal(string $originalTransactionId): static
    {
        return $this->state(fn(array $attributes) => [
            'payer_id' => $attributes['payee_id'] ?? User::factory()->create(['user_type' => \App\Enums\UserTypeEnum::COMMON])->id,
            'payee_id' => $attributes['payer_id'] ?? null,
            'type' => TransactionType::REVERSAL,
            'reverted_from' => $originalTransactionId,
            'status' => TransactionStatus::COMPLETED,
        ]);
    }
}
