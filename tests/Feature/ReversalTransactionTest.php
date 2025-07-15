<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionReversal; // Importar a Model de reversão
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionReversalStatus; // Importar o Enum de status de reversão

class ReversalTransactionTest extends TestCase
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
            'document' => $this->faker->cnpj(false), // CNPJ sem formatação
            'password' => 'password',
            'user_type' => UserTypeEnum::MERCHANT,
            'balance' => $balance,
        ]);
    }

    // --- Testes de Sucesso ---

    /**
     * Testa a reversão bem-sucedida de uma transação de TRANSFERÊNCIA.
     */
    public function testSuccessfulReversalOfTransferTransaction(): void
    {
        $payer = $this->createCommonUser(1000.00);
        $payee = $this->createCommonUser(100.00);
        $transferAmount = 200.00;
        $reasonForReversal = "Transferência incorreta por erro do usuário.";

        // 1. Simular uma transferência bem-sucedida (COMPLETED)
        // Autenticar o pagador
        $authResponse = $this->postJson('/api/login', [
            'email' => $payer->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        // Realizar a transferência
        $transferResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/transfer', [
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
        ]);

        $transferResponse->assertStatus(200);
        $originalTransactionId = $transferResponse->json('transaction.id');

        // Verificar saldos após a transferência
        $payer->refresh();
        $payee->refresh();
        $this->assertEquals(800.00, $payer->balance);
        $this->assertEquals(300.00, $payee->balance);
        $this->assertDatabaseHas('transactions', [
            'id' => $originalTransactionId,
            'status' => TransactionStatus::COMPLETED->value,
            'type' => TransactionType::TRANSFER->value,
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
        ]);

        // 2. Tentar reverter a transação
        // O usuário que reverte é o próprio pagador original
        $reversalResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken, // Mesmo token do pagador
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $originalTransactionId,
            'reason' => $reasonForReversal,
        ]);

        $reversalResponse->assertStatus(200);
        $reversalTransactionId = $reversalResponse->json('reversal_transaction.id');

        // 3. Verificar o status da transação original (deve ser REVERSED)
        $originalTransaction = Transaction::find($originalTransactionId);
        $this->assertNotNull($originalTransaction);
        $this->assertEquals(TransactionStatus::REVERSED, $originalTransaction->status);
        $this->assertEquals($reasonForReversal, $originalTransaction->reason); // Verifica se o motivo foi salvo na original

        // 4. Verificar a nova transação de reversão criada
        $reversalTransaction = Transaction::find($reversalTransactionId);
        $this->assertNotNull($reversalTransaction);
        $this->assertEquals(TransactionType::REVERSAL, $reversalTransaction->type);
        $this->assertEquals(TransactionStatus::COMPLETED, $reversalTransaction->status);
        $this->assertEquals($transferAmount, $reversalTransaction->amount);
        $this->assertEquals($originalTransactionId, $reversalTransaction->reverted_from);
        $this->assertEquals($reasonForReversal, $reversalTransaction->reason);
        $this->assertEquals($originalTransaction->payee_id, $reversalTransaction->payer_id); // Invertido
        $this->assertEquals($originalTransaction->payer_id, $reversalTransaction->payee_id); // Invertido

        // 5. Verificar saldos após a reversão
        $payer->refresh(); // Saldo do pagador deve voltar ao inicial
        $payee->refresh(); // Saldo do recebedor deve voltar ao inicial
        $this->assertEquals(1000.00, $payer->balance);
        $this->assertEquals(100.00, $payee->balance);

        // 6. Verificar o registro na tabela transaction_reversals
        $this->assertDatabaseHas('transaction_reversals', [
            'original_transaction_id' => $originalTransactionId,
            'reversal_transaction_id' => $reversalTransactionId,
            'reversed_by_user_id' => $payer->id, // Quem reverteu
            'reason' => $reasonForReversal,
            'status' => TransactionReversalStatus::COMPLETED->value,
        ]);
    }

    /**
     * Testa a reversão bem-sucedida de uma transação de DEPÓSITO.
     */
    public function testSuccessfulReversalOfDepositTransaction(): void
    {
        $user = $this->createCommonUser(100.00);
        $depositAmount = 50.00;
        $reasonForReversal = "Depósito duplicado por erro do sistema.";

        // 1. Simular um depósito bem-sucedido (COMPLETED)
        // Autenticar o usuário
        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        // Realizar o depósito
        $depositResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/deposit', [
            'amount' => $depositAmount,
        ]);

        $depositResponse->assertStatus(200);
        $originalTransactionId = $depositResponse->json('transaction.id');

        // Verificar saldo após o depósito
        $user->refresh();
        $this->assertEquals(150.00, $user->balance);
        $this->assertDatabaseHas('transactions', [
            'id' => $originalTransactionId,
            'status' => TransactionStatus::COMPLETED->value,
            'type' => TransactionType::DEPOSIT->value,
            'payee_id' => $user->id,
            'amount' => $depositAmount,
        ]);

        // 2. Tentar reverter a transação
        $reversalResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken, // Mesmo token do usuário
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $originalTransactionId,
            'reason' => $reasonForReversal,
        ]);

        $reversalResponse->assertStatus(200);
        $reversalTransactionId = $reversalResponse->json('reversal_transaction.id');

        // 3. Verificar o status da transação original (deve ser REVERSED)
        $originalTransaction = Transaction::find($originalTransactionId);
        $this->assertNotNull($originalTransaction);
        $this->assertEquals(TransactionStatus::REVERSED, $originalTransaction->status);
        $this->assertEquals($reasonForReversal, $originalTransaction->reason);

        // 4. Verificar a nova transação de reversão criada
        $reversalTransaction = Transaction::find($reversalTransactionId);
        $this->assertNotNull($reversalTransaction);
        $this->assertEquals(TransactionType::REVERSAL, $reversalTransaction->type);
        $this->assertEquals(TransactionStatus::COMPLETED, $reversalTransaction->status);
        $this->assertEquals($depositAmount, $reversalTransaction->amount);
        $this->assertEquals($originalTransactionId, $reversalTransaction->reverted_from);
        $this->assertEquals($reasonForReversal, $reversalTransaction->reason);
        // Para depósito, payer_id da reversão é o payee_id da original, e payee_id da reversão é null
        $this->assertEquals($originalTransaction->payee_id, $reversalTransaction->payer_id);
        $this->assertNull($reversalTransaction->payee_id); // Payee da reversão de depósito é nulo ou o próprio usuário que "devolveu"

        // 5. Verificar saldos após a reversão
        $user->refresh(); // Saldo do usuário deve voltar ao inicial
        $this->assertEquals(100.00, $user->balance);

        // 6. Verificar o registro na tabela transaction_reversals
        $this->assertDatabaseHas('transaction_reversals', [
            'original_transaction_id' => $originalTransactionId,
            'reversal_transaction_id' => $reversalTransactionId,
            'reversed_by_user_id' => $user->id,
            'reason' => $reasonForReversal,
            'status' => TransactionReversalStatus::COMPLETED->value,
        ]);
    }

    // --- Testes de Falha (Validações do Form Request) ---

    /**
     * Testa a tentativa de reversão com ID de transação inválido (não UUID).
     */
    public function testReversalWithInvalidTransactionIdFormat(): void
    {
        $user = $this->createCommonUser(100.00);
        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => 'not-a-valid-uuid',
            'reason' => 'Teste de ID inválido.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('original_transaction_id');
        $response->assertJson([
            'errors' => [
                'original_transaction_id' => [
                    'O ID da transação original deve ser um UUID válido.',
                ],
            ],
        ]);
    }

    /**
     * Testa a tentativa de reversão com ID de transação que não existe.
     */
    public function testReversalWithNonExistentTransactionId(): void
    {
        $user = $this->createCommonUser(100.00);
        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => '00000000-0000-0000-0000-000000000000', // UUID que não existe
            'reason' => 'Teste de ID inexistente.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('original_transaction_id');
        $response->assertJson([
            'errors' => [
                'original_transaction_id' => [
                    'A transação original não foi encontrada.',
                ],
            ],
        ]);
    }

    /**
     * Testa a tentativa de reversão sem motivo.
     */
    public function testReversalWithoutReason(): void
    {
        $user = $this->createCommonUser(100.00);
        $transaction = Transaction::factory()->create([ // Cria uma transação fictícia
            'payer_id' => $user->id,
            'payee_id' => $this->createCommonUser()->id,
            'amount' => 10.00,
            'status' => TransactionStatus::COMPLETED,
            'type' => TransactionType::TRANSFER,
        ]);

        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $transaction->id,
            'reason' => '', // Motivo vazio
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('reason');
        $response->assertJson([
            'errors' => [
                'reason' => [
                    'O motivo da reversão é obrigatório.',
                ],
            ],
        ]);
    }

    /**
     * Testa a tentativa de reversão com motivo muito curto.
     */
    public function testReversalWithShortReason(): void
    {
        $user = $this->createCommonUser(100.00);
        $transaction = Transaction::factory()->create([
            'payer_id' => $user->id,
            'payee_id' => $this->createCommonUser()->id,
            'amount' => 10.00,
            'status' => TransactionStatus::COMPLETED,
            'type' => TransactionType::TRANSFER,
        ]);

        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $transaction->id,
            'reason' => 'curto', // Menos de 10 caracteres
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('reason');
        $response->assertJson([
            'errors' => [
                'reason' => [
                    'O motivo da reversão deve ter no mínimo 10 caracteres.',
                ],
            ],
        ]);
    }

    // --- Testes de Falha (Lógica do Serviço) ---

    /**
     * Testa a tentativa de reverter uma transação que já está REVERSED.
     */
    public function testReversalOfAlreadyReversedTransaction(): void
    {
        $user = $this->createCommonUser(100.00);
        $payee = $this->createCommonUser(50.00);
        $transferAmount = 10.00;
        $reason = "Revertida anteriormente.";

        // Cria uma transação já REVERSED
        $originalTransaction = Transaction::factory()->create([
            'payer_id' => $user->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
            'status' => TransactionStatus::REVERSED, // Status já REVERSED
            'type' => TransactionType::TRANSFER,
            'reason' => $reason
        ]);

        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $originalTransaction->id,
            'reason' => 'Tentativa de re-reversão.',
        ]);

        $response->assertStatus(400); // Bad Request pelo serviço
        $response->assertJson([
            'message' => 'Falha ao realizar a reversão.',
            'error' => "Não é possível reverter uma transação com status 'REVERSED'.",
        ]);

        // Verifica que nenhuma nova transação de reversão ou registro foi criado
        $this->assertDatabaseMissing('transactions', [
            'reverted_from' => $originalTransaction->id,
            'type' => TransactionType::REVERSAL->value,
        ]);
        $this->assertDatabaseMissing('transaction_reversals', [
            'original_transaction_id' => $originalTransaction->id,
            'status' => TransactionReversalStatus::PENDING->value,
        ]);
    }

    /**
     * Testa a tentativa de reverter uma transação que está DENIED.
     */
    public function testReversalOfDeniedTransaction(): void
    {
        $user = $this->createCommonUser(100.00);
        $payee = $this->createCommonUser(50.00);
        $transferAmount = 10.00;
        $reason = "Saldo insuficiente.";

        // Cria uma transação DENIED
        $originalTransaction = Transaction::factory()->create([
            'payer_id' => $user->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
            'status' => TransactionStatus::DENIED, // Status DENIED
            'type' => TransactionType::TRANSFER,
            'reason' => $reason
        ]);

        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $originalTransaction->id,
            'reason' => 'Tentativa de reverter negada.',
        ]);

        $response->assertStatus(400); // Bad Request pelo serviço
        $response->assertJson([
            'message' => 'Falha ao realizar a reversão.',
            'error' => "Não é possível reverter uma transação com status 'DENIED'.",
        ]);

        $this->assertDatabaseMissing('transactions', [
            'reverted_from' => $originalTransaction->id,
            'type' => TransactionType::REVERSAL->value,
        ]);
        $this->assertDatabaseMissing('transaction_reversals', [
            'original_transaction_id' => $originalTransaction->id,
            'status' => TransactionReversalStatus::PENDING->value,
        ]);
    }

    /**
     * Testa a tentativa de reverter uma transação que já tem um registro de reversão.
     */
    public function testReversalOfTransactionWithExistingReversalRecord(): void
    {
        $user = $this->createCommonUser(100.00);
        $payee = $this->createCommonUser(50.00);
        $transferAmount = 10.00;
        $reason = "Motivo da reversão inicial.";

        // Cria uma transação original (digamos que está COMPLETED)
        $originalTransaction = Transaction::factory()->create([
            'payer_id' => $user->id,
            'payee_id' => $payee->id,
            'amount' => $transferAmount,
            'status' => TransactionStatus::COMPLETED,
            'type' => TransactionType::TRANSFER,
        ]);

        // Cria um registro de reversão para ela (simulando uma tentativa anterior)
        TransactionReversal::create([
            'original_transaction_id' => $originalTransaction->id,
            'reversal_transaction_id' => null, // Não importa para este teste
            'reversed_by_user_id' => $user->id,
            'reason' => $reason,
            'status' => TransactionReversalStatus::PENDING, // Ou COMPLETED, o importante é que já existe
        ]);

        $authResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $authToken = $authResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/transactions/reverse', [
            'original_transaction_id' => $originalTransaction->id,
            'reason' => 'Tentativa de reversão duplicada.',
        ]);

        $response->assertStatus(400); // Bad Request pelo serviço
        $response->assertJson([
            'message' => 'Falha ao realizar a reversão.',
            'error' => "Esta transação já possui um registro de reversão pendente ou concluída.",
        ]);

        // Verifica que nenhuma nova transação de reversão foi criada
        $this->assertDatabaseMissing('transactions', [
            'reverted_from' => $originalTransaction->id,
            'type' => TransactionType::REVERSAL->value,
        ]);
        // Apenas um registro de reversão deve existir
        $this->assertDatabaseCount('transaction_reversals', 1);
    }
}
