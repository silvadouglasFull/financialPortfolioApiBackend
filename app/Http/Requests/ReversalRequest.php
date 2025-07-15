<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReversalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Neste momento, vamos permitir que qualquer usuário autenticado tente a reversão.
        // A lógica de negócio (se apenas admins podem reverter, etc.) será tratada no ReversalService.
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
            'original_transaction_id' => [
                'required',
                'uuid',
                // Garante que a transação exista na tabela 'transactions'.
                // A validação de status e se já foi revertida será feita no serviço.
                'exists:transactions,id',
            ],
            'reason' => [
                'required',
                'string',
                'min:10', // Exige um motivo com no mínimo 10 caracteres
                'max:255', // Limita o motivo a 255 caracteres
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
            'original_transaction_id.required' => 'O ID da transação original é obrigatório.',
            'original_transaction_id.uuid' => 'O ID da transação original deve ser um UUID válido.',
            'original_transaction_id.exists' => 'A transação original não foi encontrada.',
            'reason.required' => 'O motivo da reversão é obrigatório.',
            'reason.string' => 'O motivo da reversão deve ser um texto.',
            'reason.min' => 'O motivo da reversão deve ter no mínimo :min caracteres.',
            'reason.max' => 'O motivo da reversão deve ter no máximo :max caracteres.',
        ];
    }
}
