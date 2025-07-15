<?php

namespace Database\Factories;

use App\Enums\UserTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * A senha padrão usada na factory (não precisa ser hash pois a model aplica Hash::make).
     */
    protected static ?string $password = 'SenhaForte!123';

    /**
     * Define o estado padrão do modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password,
            'document' => $this->faker->numerify('###########'), // CPF fake
            'user_type' => UserTypeEnum::COMMON,
            'balance' => 0.00,
            'google_id' => null,
        ];
    }
}
