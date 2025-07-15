<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Auth\RegisterValidationServiceInterface; // Manter para validações de unicidade
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // Para verificar se o usuário é admin
use App\Enums\UserTypeEnum; // Para verificar o tipo de usuário

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
        if ($this->has('password')) {
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
