<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Apenas usuários autenticados podem fazer depósitos.
        // A regra de negócio "apenas o próprio usuário pode fazer depósito para si"
        // será garantida no DepositService/TransactionController,
        // já que o depósito é sempre para o usuário logado.
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'gt:0', // Greater than zero
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
            'amount.required' => 'O valor do depósito é obrigatório.',
            'amount.numeric' => 'O valor do depósito deve ser um número.',
            'amount.gt' => 'O valor do depósito deve ser maior que zero.',
        ];
    }
}
