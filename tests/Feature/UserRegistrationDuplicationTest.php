<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\User\UserServiceInterface;
use App\Services\Auth\RegisterValidationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException; // Para verificar a exceção de validação
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;

class UserRegistrationDuplicationTest extends TestCase
{
    use RefreshDatabase; // Garante um banco de dados limpo para cada teste

    protected UserServiceInterface $userService;
    protected RegisterValidationServiceInterface $registerValidationService;

    protected $faker;

    /**
     * Configura o ambiente de teste antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create('pt_BR');

        // Resolvendo os serviços do container do Laravel
        $this->userService = $this->app->make(UserServiceInterface::class);
        $this->registerValidationService = $this->app->make(RegisterValidationServiceInterface::class);
    }

    /**
     * Gera um CPF válido (simplificado, para o teste).
     * Em produção, use uma biblioteca robusta.
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
     * Gera um CNPJ válido (simplificado, para o teste).
     * Em produção, use uma biblioteca robusta.
     *
     * @return string
     */
    private function generateValidCnpj(): string
    {
        // Gerar 12 digitos aleatórios
        $cnpj = str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);

        // Calcular primeiro digito verificador
        $multiplicadores1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int)$cnpj[$i] * $multiplicadores1[$i];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        $cnpj .= $digito1;

        // Calcular segundo digito verificador
        $multiplicadores2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int)$cnpj[$i] * $multiplicadores2[$i];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        $cnpj .= $digito2;

        return $cnpj;
    }

    /**
     * Testa que a criação de um usuário falha se o e-mail já existe.
     */
    public function testUserCannotBeCreatedWithDuplicateEmail(): void
    {
        // 1. Criar o primeiro usuário com um e-mail único
        $existingEmail = $this->faker->unique()->safeEmail;
        $firstUserData = [
            'name' => $this->faker->name,
            'email' => $existingEmail,
            'document' => $this->generateValidCpf(),
            'password' => 'SenhaSegura!123',
            'password_confirmation' => 'SenhaSegura!123',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        // Validar e criar o primeiro usuário (garantir que ele existe no DB)
        $validatedFirstUserData = $this->registerValidationService->validate($firstUserData);
        $this->userService->createUser($validatedFirstUserData);

        // 2. Tentar criar um segundo usuário com o mesmo e-mail
        $duplicateEmailUserData = [
            'name' => $this->faker->name,
            'email' => $existingEmail, // E-mail duplicado
            'document' => $this->generateValidCpf(), // Documento diferente
            'password' => 'OutraSenha!456',
            'password_confirmation' => 'OutraSenha!456',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        // Espera-se que uma ValidationException seja lançada
        $this->expectException(ValidationException::class);
        // REMOVIDA: $this->expectExceptionMessage('The given data was invalid.');

        try {
            $this->registerValidationService->validate($duplicateEmailUserData);
            // Se a linha acima não lançar uma exceção, o teste deve falhar
            $this->fail('Esperava-se que a validação de e-mail duplicado falhasse, mas passou.');
        } catch (ValidationException $e) {
            // Verifica se o erro específico de e-mail duplicado está presente
            $errors = $e->errors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertStringContainsString('Este e-mail já está em uso.', $errors['email'][0]);
            throw $e; // Re-lança a exceção para que PHPUnit capture e marque o teste como bem-sucedido
        }

        // Assert que o segundo usuário NÃO foi criado no banco
        $this->assertDatabaseMissing('users', ['email' => $duplicateEmailUserData['email'], 'name' => $duplicateEmailUserData['name']]);
    }

    /**
     * Testa que a criação de um usuário falha se o documento (CPF/CNPJ) já existe.
     */
    public function testUserCannotBeCreatedWithDuplicateDocument(): void
    {
        // 1. Criar o primeiro usuário com um documento único
        $existingDocument = $this->generateValidCpf(); // Ou $this->generateValidCnpj()
        $firstUserData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $existingDocument,
            'password' => 'SenhaSegura!123',
            'password_confirmation' => 'SenhaSegura!123',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        // Validar e criar o primeiro usuário (garantir que ele existe no DB)
        $validatedFirstUserData = $this->registerValidationService->validate($firstUserData);
        $this->userService->createUser($validatedFirstUserData);

        // 2. Tentar criar um segundo usuário com o mesmo documento
        $duplicateDocumentUserData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail, // E-mail diferente
            'document' => $existingDocument, // Documento duplicado
            'password' => 'OutraSenha!456',
            'password_confirmation' => 'OutraSenha!456',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        // Espera-se que uma ValidationException seja lançada
        $this->expectException(ValidationException::class);
        // REMOVIDA: $this->expectExceptionMessage('The given data was invalid.');

        try {
            $this->registerValidationService->validate($duplicateDocumentUserData);
            $this->fail('Esperava-se que a validação de documento duplicado falhasse, mas passou.');
        } catch (ValidationException $e) {
            // Verifica se o erro específico de documento duplicado está presente
            $errors = $e->errors();
            $this->assertArrayHasKey('document', $errors);
            $this->assertStringContainsString('Este documento (CPF/CNPJ) já está cadastrado.', $errors['document'][0]);
            throw $e; // Re-lança a exceção
        }

        $this->assertDatabaseMissing('users', ['document' => $duplicateDocumentUserData['document'], 'name' => $duplicateDocumentUserData['name']]);
    }

    /**
     * Testa que a criação de um usuário com e-mail e documento diferentes funciona.
     * (Teste positivo de sanity check para o trait RefreshDatabase)
     */
    public function testUserCanBeCreatedWithUniqueEmailAndDocument(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => 'SenhaSegura!123',
            'password_confirmation' => 'SenhaSegura!123',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        $validatedData = $this->registerValidationService->validate($userData);
        $createdUser = $this->userService->createUser($validatedData);

        $this->assertNotNull($createdUser);
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }
}
