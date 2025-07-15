<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;

class TransferDeniedTest extends TestCase
{
    use RefreshDatabase; // Garante um banco de dados limpo para cada teste

    protected $faker;

    /**
     * Configura o ambiente de teste antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create('pt_BR');
    }

    /**
     * Gera um CPF válido (simplificado, para o teste).
     *
     * @return string
     */
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
     * Cria um usuário do tipo COMMON para testes.
     *
     * @param float $balance
     * @return User
     */
    private function createCommonUser(float $balance = 0.00): User
    {
        return User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => 'password',
            'user_type' => UserTypeEnum::COMMON,
            'balance' => $balance,
        ]);
    }

    /**
     * Cria um usuário do tipo MERCHANT para testes.
     *
     * @param float $balance
     * @return User
     */
    private function createMerchantUser(float $balance = 0.00): User
    {
        return User::create([
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->faker->cnpj(false), // CNPJ sem formatacao
            'password' => 'password',
            'user_type' => UserTypeEnum::MERCHANT,
            'balance' => $balance,
        ]);
    }

    /**
     * Testa se um usuário do tipo lojista (MERCHANT) não pode realizar transferências.
     */
    public function testMerchantUserCannotTransfer(): void
    {
        $payer = $this->createMerchantUser(500.00); // Lojista com saldo
        $payee = $this->createCommonUser(100.00);
        $transferAmount = 100.00;
        $reason = "Usuário do tipo Lojista não autorizado a realizar transferências.";

        // Autenticar o usuário lojista
        $authResponse = $this->postJson('/api/login', [
            'email' => $payer->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        // Simular a tentativa de transferência pelo lojista
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/transfer', [
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
        ]);

        // Esperado: O status HTTP ainda é 200 OK, pois a tentativa foi registrada.
        $response->assertStatus(200);

        // Esperado: A resposta JSON indica que a transação foi negada e o motivo.
        $response->assertJson([
            'message' => 'Transferência negada.',
            'reason' => $reason,
            'transaction' => [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => number_format($transferAmount, 2, '.', ''), // formatar para 2 casas decimais
                'status' => TransactionStatus::DENIED->value, // Usar o valor raw do enum
                'type' => TransactionType::TRANSFER->value,
                'reason' => $reason,
            ],
        ]);

        // Esperado: O saldo do pagador não deve ser alterado.
        $payer->refresh();
        $this->assertEquals(500.00, $payer->balance);

        // Esperado: O saldo do recebedor não deve ser alterado.
        $payee->refresh();
        $this->assertEquals(100.00, $payee->balance);

        // Esperado: Uma transação DENIED deve ser registrada no banco de dados.
        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
            'status' => TransactionStatus::DENIED->value,
            'type' => TransactionType::TRANSFER->value,
            'reason' => $reason,
        ]);
    }

    /**
     * Testa se um usuário COMMON não pode realizar transferências com saldo insuficiente.
     */
    public function testCommonUserCannotTransferWithInsufficientBalance(): void
    {
        $payer = $this->createCommonUser(50.00); // Saldo insuficiente
        $payee = $this->createCommonUser(100.00);
        $transferAmount = 100.00; // Tentando transferir mais do que tem
        $reason = "Saldo insuficiente para realizar a transferência.";

        // Autenticar o usuário comum
        $authResponse = $this->postJson('/api/login', [
            'email' => $payer->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        // Simular a tentativa de transferência com saldo insuficiente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/transfer', [
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
        ]);

        // Esperado: O status HTTP ainda é 200 OK, pois a tentativa foi registrada.
        $response->assertStatus(200);

        // Esperado: A resposta JSON indica que a transação foi negada e o motivo.
        $response->assertJson([
            'message' => 'Transferência negada.',
            'reason' => $reason,
            'transaction' => [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => number_format($transferAmount, 2, '.', ''),
                'status' => TransactionStatus::DENIED->value,
                'type' => TransactionType::TRANSFER->value,
                'reason' => $reason,
            ],
        ]);

        // Esperado: O saldo do pagador não deve ser alterado.
        $payer->refresh();
        $this->assertEquals(50.00, $payer->balance);

        // Esperado: O saldo do recebedor não deve ser alterado.
        $payee->refresh();
        $this->assertEquals(100.00, $payee->balance);

        // Esperado: Uma transação DENIED deve ser registrada no banco de dados.
        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
            'status' => TransactionStatus::DENIED->value,
            'type' => TransactionType::TRANSFER->value,
            'reason' => $reason,
        ]);
    }
}
