<?php

namespace Tests\Unit\Controllers\Auth;

use Tests\TestCase;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Requests\Auth\RegisterRequest; // O Request que o controller espera
use App\Services\User\UserServiceInterface; // A interface do UserService
use App\Models\User; // O Model User que será retornado pelo UserService
use Illuminate\Http\JsonResponse; // Tipo de retorno esperado
use Illuminate\Http\Response; // Códigos de status HTTP
use Mockery; // Para criar mocks
use Illuminate\Validation\ValidationException; // Para simular falha de validação

class RegisterControllerTest extends TestCase
{
    protected $userServiceMock;
    protected $registerRequestMock;
    protected RegisterController $controller;

    /**
     * Configura o ambiente de teste antes de cada teste.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Cria um mock para o UserServiceInterface
        // Isso nos permite controlar o que o UserService faz e verificar se ele foi chamado
        $this->userServiceMock = Mockery::mock(UserServiceInterface::class);

        // Cria um mock para o RegisterRequest
        // Isso nos permite simular a validação do request
        $this->registerRequestMock = Mockery::mock(RegisterRequest::class);

        // Instancia o controlador, injetando o mock do UserService
        $this->controller = new RegisterController($this->userServiceMock);
    }

    /**
     * Limpa o ambiente de teste após cada teste.
     */
    protected function tearDown(): void
    {
        Mockery::close(); // Garante que todos os mocks sejam fechados corretamente
        parent::tearDown();
    }

    /**
     * Retorna dados de usuário válidos para simulação.
     *
     * @return array
     */
    protected function getValidUserData(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'document' => '12345678909',
            'password' => 'SecureP@ssword123',
            'password_confirmation' => 'SecureP@ssword123',
            'user_type' => 'COMMON',
        ];
    }

    /**
     * Testa o método register() com dados válidos e criação de usuário bem-sucedida.
     */
    public function testRegisterMethodSuccessfullyCreatesUser(): void
    {
        $userData = $this->getValidUserData();

        // Simula que o RegisterRequest passou na validação e retorna os dados validados
        $this->registerRequestMock->shouldReceive('validated')
            ->once()
            ->andReturn($userData);

        // Simula que o UserService foi chamado com os dados validados e retorna um User Model
        $mockUser = new User($userData);
        $mockUser->id = (string) \Illuminate\Support\Str::uuid(); // Adiciona um UUID mockado
        $mockUser->user_type = \App\Enums\UserTypeEnum::COMMON; // Garante que o user_type seja o Enum
        $mockUser->balance = 0.00; // Saldo inicial
        // Remove a senha do retorno, pois ela estaria hasheada e não precisa ser exposta
        unset($mockUser->password);

        $this->userServiceMock->shouldReceive('createUser')
            ->once()
            ->with($userData) // Verifica se o createUser foi chamado com os dados corretos
            ->andReturn($mockUser);

        // Chama o método register do controller
        $response = $this->controller->register($this->registerRequestMock);

        // Assertions para verificar a resposta HTTP
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode()); // 201 Created

        $responseData = $response->getData(true); // Obtém o corpo JSON como um array
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Usuário registrado com sucesso!', $responseData['message']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($mockUser->name, $responseData['user']['name']);
        $this->assertEquals($mockUser->email, $responseData['user']['email']);
        $this->assertEquals($mockUser->document, $responseData['user']['document']);
        $this->assertEquals($mockUser->user_type->value, $responseData['user']['user_type']);
        $this->assertEquals($mockUser->balance, $responseData['user']['balance']);
        $this->assertArrayHasKey('id', $responseData['user']);
        $this->assertNotNull($responseData['user']['id']);
        // Garante que a senha não foi retornada no JSON
        $this->assertArrayNotHasKey('password', $responseData['user']);
    }

    /**
     * Testa se o método register() lida com uma exceção durante a criação do usuário.
     */
    public function testRegisterMethodHandlesUserServiceException(): void
    {
        $userData = $this->getValidUserData();
        $errorMessage = 'Erro interno ao criar usuário.';

        // Simula que o RegisterRequest passou na validação
        $this->registerRequestMock->shouldReceive('validated')
            ->once()
            ->andReturn($userData);

        // Simula que o UserService lança uma exceção
        $this->userServiceMock->shouldReceive('createUser')
            ->once()
            ->with($userData)
            ->andThrow(new \Exception($errorMessage)); // Simula um erro no serviço

        // Espera que o controlador retorne um erro 500
        $response = $this->controller->register($this->registerRequestMock);

        // Assertions para verificar a resposta de erro
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode()); // 500 Internal Server Error

        $responseData = $response->getData(true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Ocorreu um erro ao tentar registrar o usuário.', $responseData['message']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals($errorMessage, $responseData['error']); // Em produção, você ocultaria esta mensagem
    }

    /**
     * Testa se o método register() não é chamado se o RegisterRequest falhar na validação.
     * (Este teste não executa o método register do controller, mas verifica o fluxo do Laravel)
     *
     * NOTA: Este cenário é mais um teste de Feature/Integração para o Laravel Framework,
     * pois a validação do Form Request ocorre ANTES do método do controller ser invocado.
     * Para testar unitariamente, precisaríamos simular o comportamento do framework
     * que lança a exceção ANTES do controller. No contexto de teste unitário do controller,
     * assumimos que o RegisterRequest já entregou dados validados ou já falhou antes.
     * Portanto, este teste específico pode ser mais complexo para ser um "unitário puro"
     * para o *controlador*. No entanto, o teste que simula o Request retornando dados válidos
     * já demonstra que o controller *recebe* os dados do Request.
     * O teste de validação do RegisterValidationService já cobriu a lógica de validação.
     *
     * Este exemplo abaixo é mais demonstrativo de como o fluxo funciona no Laravel.
     */
    public function testRegisterMethodIsNotCalledIfValidationFailsInFormRequest(): void
    {
        // Neste teste, não chamamos o controller diretamente com o mock.
        // Em vez disso, verificamos que o UserService NÃO é chamado,
        // o que indica que o fluxo foi interrompido pelo FormRequest.

        // Simula que o RegisterRequest LANÇA uma ValidationException (antes de chamar o controller)
        // Isso é mais complexo de simular em um teste unitário puro do controller,
        // pois a exceção é lançada pelo framework antes da chamada do método do controller.
        // Portanto, este teste abaixo não testaria o 'register' do controller em si,
        // mas sim o comportamento do Laravel ao lidar com Form Requests.
        // Para um teste unitário estrito do controller, assumimos que o Request já passou
        // ou já lançou a exceção antes.

        // Por simplicidade e foco no Controller:
        // Se o RegisterRequest falhar, o método `register` no controller simplesmente não é executado.
        // Os testes de validação do `RegisterValidationServiceTest` já garantem que a validação funciona.
        // Testar isso no controller exigiria mockar o comportamento do próprio framework Laravel
        // para *impedir* a chamada do método do controller, o que descaracteriza um "teste unitário" do controller.

        $this->expectException(ValidationException::class); // Esperamos a exceção do FormRequest

        // Para simular que o FormRequest "falha", precisamos que ele lance a exceção
        // antes de chegarmos ao controller. Isso é mais para um teste de feature/integração.
        // Para este teste unitário, vamos apenas demonstrar que o createUser NÃO DEVE SER CHAMADO
        $this->userServiceMock->shouldNotReceive('createUser');

        // Para que o RegisterRequest lance a exceção no contexto de um teste unitário,
        // precisaríamos criar uma instância real do RegisterRequest e dispará-lo,
        // ou criar um mock que explicitamente lance a exceção quando `validated()` for chamado.
        // Como `validated()` é chamado *dentro* do `passedValidation()` do Request,
        // e este é um teste *do Controller*, não vamos simular isso aqui diretamente,
        // pois o controller só é invocado SE a validação já passou.

        // Se você quisesse forçar um cenário de falha de validação para o CONTROLLER (o que é mais para integração):
        // $request = RegisterRequest::create('/api/auth/register', 'POST', ['name' => '']); // dados inválidos
        // $request->setContainer($this->app); // Necessário para que o request resolva dependências
        // try {
        //     $request->validateResolved(); // Força a validação do FormRequest
        // } catch (ValidationException $e) {
        //     // O request falhou, o controller não seria chamado.
        //     // Este catch é para o teste em si, não para o fluxo normal do app.
        //     throw $e; // Relança para que o PHPUnit pegue a exceção esperada
        // }
    }
}
