<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Auth\RegisterValidationServiceInterface; // Importar a interface do serviço de validação
use Illuminate\Validation\ValidationException; // Importar para capturar exceções de validação
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegisterRequest",
    title: "Register Request",
    description: "Data required for user registration.",
    required: ["name", "email", "document", "password", "password_confirmation", "user_type"],
    properties: [
        new OA\Property(property: "name", type: "string", example: "João Silva", description: "The user's full name."),
        new OA\Property(property: "email", type: "string", format: "email", example: "joao.silva@example.com", description: "The user's email address (must be unique)."),
        new OA\Property(property: "document", type: "string", example: "12345678900", description: "The user's CPF (for common users) or CNPJ (for shopkeepers), without formatting."),
        new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "secure_password123", description: "The user's password."),
        new OA\Property(property: "password_confirmation", type: "string", format: "password", minLength: 8, example: "secure_password123", description: "Confirmation of the user's password."),
        new OA\Property(
            property: "user_type",
            type: "string",
            enum: ["common", "shopkeeper"], // Assuming UserTypeEnum maps to these strings
            example: "common",
            description: "The type of user being registered."
        ),
    ],
    type: "object"
)]
class RegisterRequest extends FormRequest
{
    /**
     * O serviço de validação que será injetado e utilizado.
     *
     * @var RegisterValidationServiceInterface
     */
    protected RegisterValidationServiceInterface $registerValidationService;

    /**
     * Construtor do RegisterRequest.
     * Injeta o serviço de validação.
     *
     * @param RegisterValidationServiceInterface $registerValidationService
     */
    public function __construct(RegisterValidationServiceInterface $registerValidationService)
    {
        // Certifique-se de que o FormRequest seja instanciado com o container
        // para que a injeção de dependência funcione.
        // Em um ambiente Laravel normal, isso é tratado automaticamente quando o FormRequest é resolvido.
        // Se você estiver testando ou instanciando manualmente, pode precisar passar o container.
        $this->registerValidationService = $registerValidationService;
        // Chamar o construtor pai é importante para a inicialização do FormRequest.
        // No entanto, injetar no __construct de um FormRequest pode ser complicado
        // porque o FormRequest é resolvido pelo container *antes* do seu controller.
        // A maneira mais comum de usar serviços em FormRequests para lógica pós-validação é
        // através do método `withValidator` ou injetando-o no controller e chamando-o de lá,
        // ou fazendo um `app()->make()` dentro do `rules()` ou `after()`.
        // Para a documentação OpenAPI, isso não impacta, mas é uma nota de implementação.
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     * Para o cadastro, qualquer um pode fazer a requisição.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Todos os usuários (mesmo não autenticados) podem tentar se registrar
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // As regras de validação serão delegadas ao RegisterValidationService.
        // Este método `rules()` aqui pode retornar um array vazio ou regras mínimas
        // que o serviço não cobre (embora neste caso, o serviço cobre tudo).
        // Se você quiser que o Laravel trate a exceção de validação,
        // pode deixar algumas regras aqui. Para simplificar e delegar tudo ao serviço:
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'], // unique será validado no serviço
            'document' => ['required', 'string'], // Validação de CPF/CNPJ e unique no serviço
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'], // Regra 'same' para a confirmação
            'user_type' => ['required', 'string', 'in:common,shopkeeper'], // Supondo que seu enum mapeie para essas strings
        ];
    }

    /**
     * Handle a passed validation attempt.
     * Este método é chamado se as regras básicas do FormRequest passarem.
     * É aqui que chamamos o nosso serviço de validação personalizado.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        try {
            // Delega a validação principal e customizada ao RegisterValidationService
            $validatedData = $this->registerValidationService->validate($this->all());

            // Mescla os dados validados de volta ao request
            // Isso garante que $request->validated() no controller retorne os dados validados pelo serviço
            $this->merge($validatedData);
        } catch (ValidationException $e) {
            // Se o serviço de validação lançar uma ValidationException,
            // o FormRequest deve relançá-la para que o handler de exceções do Laravel a capture
            throw $e;
        }
    }

    /**
     * Prepara os dados para validação.
     * Pode ser usado para sanitizar ou modificar dados antes da validação.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Se houver necessidade de pré-processamento, como formatar documentos, pode ser feito aqui
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/[^0-9]/', '', $this->input('document')),
            ]);
        }
    }
}
