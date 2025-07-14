<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Auth\RegisterValidationServiceInterface; // Importar a interface do serviço de validação
use Illuminate\Validation\ValidationException; // Importar para capturar exceções de validação

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
        parent::__construct(); // Chama o construtor da classe pai
        $this->registerValidationService = $registerValidationService;
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
            'name' => ['required'], // Regra mínima para passar para o serviço
            'email' => ['required'],
            'document' => ['required'],
            'password' => ['required'],
            'password_confirmation' => ['required', 'same:password'], // Regra 'same' para a confirmação
            'user_type' => ['required'],
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
