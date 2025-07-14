<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Enums\UserTypeEnum;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

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
     * Testa se um usuário pode fazer login com credenciais válidas.
     */
    public function testUserCanLoginWithValidCredentials(): void
    {
        // Gerar dados de usuário válidos
        $password = 'SenhaSegura!123'; // Senha em texto plano para o teste
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => $this->generateValidCpf(),
            'password' => $password, // Deixe o mutator da Model User hashear a senha
            'user_type' => UserTypeEnum::COMMON, // Usar o Enum diretamente
            'balance' => 0.00,
        ];

        // 1. Criar o usuário diretamente via Model (simulando um usuário já cadastrado)
        $user = User::create($userData);

        // 2. Simular a chamada de login para a rota /api/login
        // Usamos o método postJson() para enviar requisições JSON
        $response = $this->postJson('/api/login', [
            'email' => $userData['email'],
            'password' => $password, // Enviar a senha em texto plano como seria em uma requisição real
        ]);
        // 3. Comparar os resultados
        // O status HTTP esperado é 200 OK para um login bem-sucedido
        $response->assertStatus(200);

        // A resposta JSON deve conter o token e os dados do usuário
        $response->assertJsonStructure([
            'message',
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'document',
                'user_type',
                'balance',
            ],
        ]);

        // Verificar o conteúdo da resposta
        $responseData = $response->json();
        $this->assertEquals('Login realizado com sucesso!', $responseData['message']);
        $this->assertNotNull($responseData['token']);
        $this->assertIsString($responseData['token']);

        // Verificar se os dados do usuário retornado correspondem ao usuário criado
        $this->assertEquals($user->id, $responseData['user']['id']);
        $this->assertEquals($user->name, $responseData['user']['name']);
        $this->assertEquals($user->email, $responseData['user']['email']);
        $this->assertEquals($user->document, $responseData['user']['document']);
        $this->assertEquals($user->user_type->value, $responseData['user']['user_type']);
        $this->assertEquals(number_format($user->balance, 2, '.', ''), number_format($responseData['user']['balance'], 2, '.', ''));
        $this->assertArrayNotHasKey('password', $responseData['user']);

        // Opcional: Verificar se o token é válido tentando acessar uma rota protegida
        $token = $responseData['token'];
        $protectedResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $protectedResponse->assertStatus(200);
        $protectedResponse->assertJsonFragment([
            'email' => $user->email,
        ]);
    }
}
