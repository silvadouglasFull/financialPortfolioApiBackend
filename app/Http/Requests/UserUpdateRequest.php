<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importar para a regra 'Rule::unique'
use Illuminate\Support\Facades\Auth;
use App\Enums\UserTypeEnum;
use App\Models\User; // Importar o modelo User para type-hinting

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Somente administradores podem atualizar usuários.
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
        // O usuário que está sendo atualizado (vem da rota via injeção de modelo)
        // Certifique-se de que sua rota web para atualização tenha um parâmetro de rota como {user}
        // Ex: Route::put('/users/{user}', [UserController::class, 'update']);
        $userId = $this->route('user')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255'],
            // Regra 'unique' com ignorância do ID do usuário atual
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            // Regra 'unique' com ignorância do ID do usuário atual
            'document' => ['required', 'string', 'max:14', Rule::unique('users', 'document')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // 'nullable' porque a senha pode não ser alterada
            'user_type' => ['required', 'string', 'in:' . implode(',', [UserTypeEnum::COMMON->value, UserTypeEnum::MERCHANT->value, UserTypeEnum::ADMIN->value])],
        ];
    }

    /**
     * Prepare the data for validation.
     * Formata o documento e hasheia a senha (se fornecida) antes da validação.
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

        // Hasheia a senha SOMENTE SE ela for fornecida (nullable)
        if ($this->has('password') && $this->input('password') !== null) {
            $this->merge([
                'password' => bcrypt($this->input('password')),
            ]);
        }
    }
}
