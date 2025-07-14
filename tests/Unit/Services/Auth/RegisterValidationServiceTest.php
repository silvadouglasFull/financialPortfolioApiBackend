<?php

namespace Tests\Unit\Services\Auth;

use Tests\TestCase;
use App\Services\Auth\RegisterValidationService;
use Illuminate\Validation\ValidationException;
use App\Enums\UserTypeEnum; // Para acessar os valores do Enum
use Mockery; // Se precisar de mocks, mas para este teste direto, talvez não seja necessário inicialmente

class RegisterValidationServiceTest extends TestCase
{
    protected RegisterValidationService $service;

    /**
     * Configura o ambiente de teste antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Instancia o serviço de validação.
        // Como ele não tem dependências injetadas no construtor para validações,
        // podemos instanciá-lo diretamente.
        $this->service = new RegisterValidationService();
    }

    /**
     * Limpa o ambiente de teste após cada teste.
     */
    protected function tearDown(): void
    {
        Mockery::close(); // Garante que mocks sejam fechados
        parent::tearDown();
    }

    /**
     * Retorna um conjunto de dados válidos para o registro.
     *
     * @return array
     */
    protected function getValidUserData(): array
    {
        return [
            'name' => 'Teste Usuário',
            'email' => 'teste@example.com',
            'document' => '12345678909', // CPF válido de teste
            'password' => 'Senha@Segura123',
            'password_confirmation' => 'Senha@Segura123',
            'user_type' => UserTypeEnum::COMMON->value,
        ];
    }

    /**
     * Testa se o serviço de validação retorna os dados validados para dados válidos.
     * (Este é um teste positivo para ter certeza que a validação passa quando deve)
     */
    public function testValidationPassesWithValidData(): void
    {
        $data = $this->getValidUserData();

        // O serviço deve retornar os dados validados sem lançar exceção
        $validatedData = $this->service->validate($data);

        $this->assertIsArray($validatedData);
        $this->assertArrayHasKey('name', $validatedData);
        $this->assertEquals($data['name'], $validatedData['name']);
        // ... adicione mais asserções para todos os campos validados
    }

    /**
     * Testa se a validação falha para campos obrigatórios ausentes.
     */
    public function testValidationFailsWhenRequiredFieldsAreMissing(): void
    {
        $this->expectException(ValidationException::class);

        // Dados sem o campo 'name'
        $data = $this->getValidUserData();
        unset($data['name']);
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para um e-mail malformado.
     */
    public function testValidationFailsWithMalformedEmail(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['email'] = 'email-invalido'; // E-mail sem o formato correto
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para um CPF inválido.
     */
    public function testValidationFailsWithInvalidCpf(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['document'] = '11111111111'; // CPF inválido (todos os dígitos iguais)
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para um CNPJ inválido.
     */
    public function testValidationFailsWithInvalidCnpj(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['document'] = '00000000000000'; // CNPJ inválido (todos os dígitos iguais)
        $data['user_type'] = UserTypeEnum::MERCHANT->value; // Lojista com CNPJ
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para uma senha muito curta.
     */
    public function testValidationFailsWithTooShortPassword(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['password'] = 'short'; // Senha com menos de 8 caracteres
        $data['password_confirmation'] = 'short';
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para uma senha fraca (sem maiúscula).
     */
    public function testValidationFailsWithWeakPasswordMissingUppercase(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['password'] = 'senhasegura123@'; // Falta maiúscula
        $data['password_confirmation'] = 'senhasegura123@';
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para uma senha fraca (sem número).
     */
    public function testValidationFailsWithWeakPasswordMissingNumber(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['password'] = 'SenhaSegura@'; // Falta número
        $data['password_confirmation'] = 'SenhaSegura@';
        $this->service->validate($data);
    }

    /**
     * Testa se a validação falha para um user_type inválido.
     */
    public function testValidationFailsWithInvalidUserType(): void
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidUserData();
        $data['user_type'] = 'INVALID_TYPE'; // Tipo de usuário não existente no Enum
        $this->service->validate($data);
    }

    // Você pode adicionar mais testes para cenários específicos como:
    // - E-mail já existente (exigiria um mock do banco de dados ou um teste de integração)
    // - Documento já existente (exigiria mock ou teste de integração)
    // - Senha sem caractere especial
    // - Confirmação de senha diferente
}
