<?php

namespace App\Http\Requests;

use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Auth\RegisterValidationServiceInterface; // Manter para validações de unicidade
use Illuminate\Support\Facades\Auth; // Para verificar se o usuário é admin
use OpenApi\Attributes as OA; // Import the OpenApi Attributes

#[OA\Schema(
    schema: "UserStoreRequest",
    title: "User Store Request",
    description: "Data required to create a new user by an administrator.",
    required: ["name", "email", "document", "password", "password_confirmation", "user_type"],
    properties: [
        new OA\Property(
            property: "name",
            type: "string",
            maxLength: 255,
            example: "Novo Admin",
            description: "The full name of the user."
        ),
        new OA\Property(
            property: "email",
            type: "string",
            format: "email",
            maxLength: 255,
            example: "new.admin@example.com",
            description: "The unique email address for the user."
        ),
        new OA\Property(
            property: "document",
            type: "string",
            maxLength: 14,
            example: "12345678901234",
            description: "The unique document number (CPF or CNPJ) for the user. Only digits."
        ),
        new OA\Property(
            property: "password",
            type: "string",
            format: "password",
            minLength: 8,
            example: "StrongP@ssw0rd",
            description: "The user's password. Must be at least 8 characters and confirmed."
        ),
        new OA\Property(
            property: "password_confirmation",
            type: "string",
            format: "password",
            minLength: 8,
            example: "StrongP@ssw0rd",
            description: "Confirmation of the user's password. Must match 'password'."
        ),
        new OA\Property(
            property: "user_type",
            type: "string",
            enum: ["common", "shopkeeper", "admin"],
            example: "admin",
            description: "The type of user being created."
        )
    ],
    type: "object"
)]
class UserStoreRequest extends FormRequest
{
    /**
     * O serviço de validação que será injetado e utilizado.
     *
     * @var RegisterValidationServiceInterface
     */
    protected RegisterValidationServiceInterface $registerValidationService;

    /**
     * Construtor do UserStoreRequest.
     * Injeta o serviço de validação.
     *
     * @param RegisterValidationServiceInterface $registerValidationService
     */
    public function __construct(RegisterValidationServiceInterface $registerValidationService)
    {
        parent::__construct();
        $this->registerValidationService = $registerValidationService;
    }

    /**
     * Determine if the user is authorized to make this request.
     * Somente administradores podem criar usuários via este formulário.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Verifica se o usuário está autenticado E se ele é um administrador
        return Auth::check() && Auth::user()->user_type === UserTypeEnum::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // Validação de unicidade aqui
            'document' => ['required', 'string', 'max:14', 'unique:users,document'], // Validação de unicidade aqui
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' exige password_confirmation
            'user_type' => ['required', 'string', 'in:' . implode(',', [UserTypeEnum::COMMON->value, UserTypeEnum::MERCHANT->value, UserTypeEnum::ADMIN->value])], // Valida os tipos de usuário permitidos
        ];
    }

    /**
     * Prepare the data for validation.
     * Formata o documento e hasheia a senha antes da validação.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Formata o documento (remove caracteres não numéricos)
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/[^0-9]/', '', $this->input('document')),
            ]);
        }

        // Hasheia a senha ANTES de ser validada e passada para o controller.
        // Isso é importante para que a regra 'confirmed' funcione com a senha já hasheada
        // e para que você não precise hashear no Service ou Controller.
        if ($this->has('password') && !empty($this->input('password'))) {
            $this->merge([
                'password' => bcrypt($this->input('password')),
            ]);
        }
    }

    /**
     * Handle a passed validation attempt.
     * Este método é chamado se as regras básicas do FormRequest passarem.
     * Usamos o serviço de validação para regras de negócio mais complexas (se houver, além do 'unique').
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Se houver regras de validação personalizadas no RegisterValidationService
        // que não podem ser expressas diretamente nas regras 'rules()',
        // você as chamaria aqui.
        // Por exemplo, uma validação cruzada entre user_type e documento,
        // ou uma validação externa.
        // Por enquanto, as regras 'unique' no rules() já cobrem o principal para a criação.
    }
}
