<?php

namespace Tests\Feature;

use App\Enums\UserTypeEnum;
use Tests\TestCase;
use App\Models\User;
use App\Services\User\UserServiceInterface;
use App\Services\Auth\RegisterValidationServiceInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase; // Importar o trait RefreshDatabase
use Faker\Factory as Faker; // Para usar o Faker

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase; // Garante um banco de dados limpo para cada teste

    protected UserServiceInterface $userService;
    protected RegisterValidationServiceInterface $registerValidationService; // Injetar para validação
    protected UserRepositoryInterface $userRepository; // Injetar para buscar no banco

    protected $faker;

    /**
     * Configura o ambiente de teste antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Inicializa o Faker
        $this->faker = Faker::create('pt_BR'); // Usar localidade brasileira para CPF/CNPJ

        // Resolvendo os serviços e repositórios do container do Laravel
        // Isso garante que estamos usando as implementações reais com o banco de dados
        $this->userService = $this->app->make(UserServiceInterface::class);
        $this->registerValidationService = $this->app->make(RegisterValidationServiceInterface::class);
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);

        // O trait RefreshDatabase cuida das migrações e limpezas
        // Mas se suas migrations não estiverem rodando automaticamente ou se você
        // precisar de seeders específicos, pode precisar de `$this->artisan('migrate');`
        // ou `$this->seed()`. RefreshDatabase já faz o migrate.
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
     * Testa a criação de um usuário com dados válidos.
     */
    public function testUserCanBeCreatedWithValidData(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(), // Ou $this->generateValidCnpj()
            'password' => 'SenhaSegura!123', // Senha forte para passar validação
            'password_confirmation' => 'SenhaSegura!123',
            'user_type' => UserTypeEnum::COMMON->value,
        ];

        // 1. Validar os dados usando o serviço de validação
        // Isso garante que os dados gerados pelo Faker são aceitos pelo seu serviço.
        try {
            $validatedData = $this->registerValidationService->validate($userData);
        } catch (ValidationException $e) {
            $this->fail("Os dados gerados pelo Faker não passaram na validação: " . json_encode($e->errors()));
        }

        // 2. Criar o usuário usando o UserService
        // Este é o método que testamos se realmente cria no banco.
        $createdUser = $this->userService->createUser($validatedData);

        // 3. Recuperar o registro do banco de dados usando o ID
        $foundUser = $this->userRepository->findById($createdUser->id);

        // 4. Compara os dados
        $this->assertNotNull($foundUser, 'Usuário não encontrado no banco de dados após a criação.');
        $this->assertInstanceOf(User::class, $foundUser);

        // Compara os atributos (excluindo campos que mudam, como password hasheada ou timestamps)
        $this->assertEquals($userData['name'], $foundUser->name);
        $this->assertEquals($userData['email'], $foundUser->email);
        // O documento pode ter sido normalizado (removendo pontos/hifens) pelo seu serviço de validação.
        // Compare com o documento normalizado, se for o caso.
        $this->assertEquals(preg_replace('/[^0-9]/', '', $userData['document']), $foundUser->document);
        $this->assertEquals(UserTypeEnum::COMMON, $foundUser->user_type); // Compara com o Enum
        $this->assertEquals(0.00, $foundUser->balance); // Verifica o saldo inicial

        // Verifique se a senha foi hasheada corretamente (não compare a string original)
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check($userData['password'], $foundUser->password),
            'Senha não foi hasheada ou verificada corretamente.'
        );
    }
}
