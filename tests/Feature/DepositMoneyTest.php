<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;
use PHPUnit\Framework\Attributes\DataProvider; // Importar o atributo DataProvider

class DepositMoneyTest extends TestCase
{
    use RefreshDatabase;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create('pt_BR');
    }

    private function generateValidCpf(): string
    {
        // Gerar 9 digitos aleatórios
        $cpf = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);

        // Calcular primeiro digito verificador
        for ($i = 0, $soma = 0; $i < 9; $i++) {
            $soma += (int)$cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        // Adicionar primeiro digito
        $cpf .= $digito1;

        // Calcular segundo digito verificador
        for ($i = 0, $soma = 0; $i < 10; $i++) {
            $soma += (int)$cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        // Adicionar segundo digito e retornar
        $cpf .= $digito2;

        return $cpf;
    }

    /**
     * Testa se o depósito com valor zero ou negativo é impedido.
     */
    #[DataProvider('invalidDepositAmountsProvider')] // Usar o atributo PHP para DataProvider
    public function testDepositWithInvalidAmount(float $invalidAmount): void
    {
        // 1. Criar um usuário com qualquer saldo inicial
        $initialBalance = 100.00;
        $userPassword = 'senhaSeguraU123';
        $user = User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => $userPassword,
            'user_type' => UserTypeEnum::COMMON,
            'balance' => $initialBalance,
        ]);

        // 2. Autenticar o usuário para a requisição
        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $userPassword,
        ]);
        $authToken = $authResponse->json('token');

        // 3. Simular depósito com valor inválido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/deposit', [
            'amount' => $invalidAmount,
        ]);

        // 4. Esperado: A API deve retornar status 422 (Unprocessable Entity) devido a erro de validação
        $response->assertStatus(422);

        // 5. Esperado: Nenhuma transação deve ser registrada no banco de dados
        // Verificamos que não há transações para este usuário com este valor específico
        $this->assertFalse(Transaction::where('payee_id', $user->id)
            ->where('amount', $invalidAmount)
            ->exists(), "Transação com valor inválido ($invalidAmount) não deveria ter sido registrada.");

        // 6. Esperado: O saldo do usuário não deve ser alterado
        $user->refresh(); // Recarrega o usuário do banco de dados
        $this->assertEquals($initialBalance, $user->balance, "O saldo do usuário não deveria ter sido alterado.");

        // 7. Verificar a mensagem de erro específica para o campo 'amount'
        $response->assertJsonValidationErrors('amount');
        $response->assertJson([
            'errors' => [
                'amount' => [
                    'O valor do depósito deve ser maior que zero.', // Ajustar para a mensagem exata com ponto final
                ],
            ],
        ]);
    }

    /**
     * Data provider para valores de depósito inválidos.
     */
    public static function invalidDepositAmountsProvider(): array
    {
        return [
            'zero amount' => [0.00],
            'negative amount' => [-50.00],
        ];
    }

    /**
     * Testa se um usuário não autenticado não pode fazer depósitos.
     */
    public function testUnauthenticatedUserCannotDeposit(): void
    {
        $response = $this->postJson('/api/transactions/deposit', [
            'amount' => 100.00,
        ]);

        $response->assertStatus(401); // 401 Unauthorized
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
