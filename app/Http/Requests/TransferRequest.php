<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importar para usar a classe Rule
use App\Enums\UserTypeEnum; // Importar o UserTypeEnum

/**
 * Class TransferRequest
 *
 * Lida com as regras de validação para a requisição de transferência de dinheiro.
 */
class TransferRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // O usuário deve estar autenticado para fazer uma transferência.
        // Além disso, usuários do tipo MERCHANT não podem fazer transferências (ser pagadores).
        if (!$this->user()) {
            return false; // Não autenticado
        }

        // Verifica se o usuário autenticado não é do tipo MERCHANT
        // Acesso ao user_type via Enum, garantindo type-safety
        if ($this->user()->user_type === UserTypeEnum::MERCHANT) {
            return false; // Usuários MERCHANT não podem ser pagadores
        }

        return true;
    }

    /**
     * Retorna as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payee_id' => [
                'required',
                'uuid',
                'exists:users,id',
                // Garante que o payee_id não é o mesmo do usuário logado (pagador)
                Rule::notIn([$this->user()->id]),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0', // Greater than zero
                'decimal:0,2', // Permite 0 a 2 casas decimais (para valores monetários)
            ],
        ];
    }

    /**
     * Retorna mensagens de erro personalizadas para as regras de validação.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'payee_id.required' => 'O ID do recebedor é obrigatório.',
            'payee_id.uuid' => 'O ID do recebedor deve ser um UUID válido.',
            'payee_id.exists' => 'O recebedor com o ID fornecido não foi encontrado.',
            'payee_id.not_in' => 'Você não pode transferir dinheiro para si mesmo.',
            'amount.required' => 'O valor da transferência é obrigatório.',
            'amount.numeric' => 'O valor da transferência deve ser um número.',
            'amount.gt' => 'O valor da transferência deve ser maior que zero.',
            'amount.decimal' => 'O valor da transferência deve ter no máximo duas casas decimais.',
        ];
    }

    /**
     * Prepara os dados para validação.
     * Isso pode ser útil para normalizar dados antes da validação.
     */
    protected function prepareForValidation(): void
    {
        // Se o amount vier como string com vírgula (ex: "100,50"), converte para ponto.
        // Isso é comum em sistemas que lidam com padrões de localização diferentes.
        if (isset($this->amount) && is_string($this->amount)) {
            $this->merge([
                'amount' => str_replace(',', '.', $this->amount)
            ]);
        }
    }
}
