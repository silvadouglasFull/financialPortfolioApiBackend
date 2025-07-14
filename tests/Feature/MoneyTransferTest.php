<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;
use App\Enums\TransactionStatus; // Importar o Enum de status da transação

class MoneyTransferTest extends TestCase
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
     * Testa se uma transferência de dinheiro é realizada com sucesso com saldo suficiente.
     */
    public function testMoneyTransferWithSufficientBalance(): void
    {
        // 1. Criar usuário A (pagador) com saldo suficiente
        $payerInitialBalance = 500.00;
        $payerPassword = 'senhaSeguraA123';
        $payer = User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => $payerPassword, // O mutator da Model irá hashear
            'user_type' => UserTypeEnum::COMMON,
            'balance' => $payerInitialBalance,
        ]);

        // 2. Criar usuário B (recebedor)
        $payeeInitialBalance = 100.00;
        $payee = User::create([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => 'senhaSeguraB123',
            'user_type' => UserTypeEnum::COMMON,
            'balance' => $payeeInitialBalance,
        ]);

        // 3. Autenticar o usuário pagador para a requisição
        // Isso simula o login do usuário A e obtém um token Sanctum
        $authResponse = $this->postJson('/api/login', [
            'email' => $payer->email,
            'password' => $payerPassword,
        ]);
        $authToken = $authResponse->json('token');

        // Valor da transferência
        $transferAmount = 150.00;

        // 4. Simular a requisição de transferência
        // Usar o token obtido para autenticar a requisição POST
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/transfer', [
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
        ]);

        // 5. Esperar o status 200 OK
        $response->assertStatus(200);

        // 6. Verificar a estrutura da resposta e a mensagem de sucesso
        $response->assertJsonStructure([
            'message',
            'transaction' => [
                'id',
                'payer_id',
                'payee_id',
                'amount',
                'status',
                'created_at',
                'updated_at',
                'payer', // Devem vir os dados do pagador
                'payee', // Devem vir os dados do recebedor
            ],
        ]);
        $response->assertJson([
            'message' => 'Transferência realizada com sucesso!',
        ]);

        // 7. Verificar os saldos finais no banco de dados
        // Recarregar os usuários para obter os saldos atualizados
        $payer->refresh();
        $payee->refresh();

        $this->assertEquals($payerInitialBalance - $transferAmount, $payer->balance);
        $this->assertEquals($payeeInitialBalance + $transferAmount, $payee->balance);

        // 8. Verificar se a transação foi registrada corretamente no banco de dados
        $transaction = Transaction::where('payer_id', $payer->id)
            ->where('payee_id', $payee->id)
            ->where('amount', $transferAmount)
            ->first();

        $this->assertNotNull($transaction, 'A transação não foi encontrada no banco de dados.');
        $this->assertEquals(TransactionStatus::COMPLETED, $transaction->status); // Verifica o status do Enum

        // 9. Opcional: Verificar o JSON retornado da transação
        $responseData = $response->json('transaction');
        $this->assertEquals($transaction->id, $responseData['id']);
        $this->assertEquals($payer->id, $responseData['payer_id']);
        $this->assertEquals($payee->id, $responseData['payee_id']);
        $this->assertEquals(number_format($transferAmount, 2, '.', ''), number_format($responseData['amount'], 2, '.', ''));
        $this->assertEquals(TransactionStatus::COMPLETED->value, $responseData['status']);
        $this->assertEquals($payer->email, $responseData['payer']['email']);
        $this->assertEquals($payee->email, $responseData['payee']['email']);
    }
}
