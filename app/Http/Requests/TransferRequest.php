<?php

namespace App\Http\Requests;

use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Se não houver usuário autenticado, a autorização falha.
        if (!$user) {
            return false;
        }

        // Permite que usuários COMMON e MERCHANT passem por aqui.
        // A lógica específica de negação para MERCHANT será tratada no TransferService
        // para que a transação DENIED seja registrada.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'payee_id' => [
                'required',
                'string',
                'exists:users,id',
                'not_in:' . $this->user()->id, // Não pode transferir para si mesmo
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0', // Valor deve ser maior que zero
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payee_id.required' => 'O ID do recebedor é obrigatório.',
            'payee_id.string' => 'O ID do recebedor deve ser uma string.',
            'payee_id.exists' => 'O recebedor especificado não foi encontrado.',
            'payee_id.not_in' => 'Você não pode transferir dinheiro para si mesmo.',
            'amount.required' => 'O valor da transferência é obrigatório.',
            'amount.numeric' => 'O valor da transferência deve ser um número.',
            'amount.gt' => 'O valor da transferência deve ser maior que zero.',
        ];
    }
}
