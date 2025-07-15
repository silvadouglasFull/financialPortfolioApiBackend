<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importar para a regra 'Rule::unique'
use Illuminate\Support\Facades\Auth;
use App\Enums\UserTypeEnum;
use OpenApi\Attributes as OA; // Import the OpenApi Attributes

#[OA\Schema(
    schema: "UserUpdateRequest",
    title: "User Update Request",
    description: "Data required to update an existing user by an administrator.",
    required: ["name", "email", "document", "user_type"], // Password is nullable
    properties: [
        new OA\Property(
            property: "name",
            type: "string",
            maxLength: 255,
            example: "Nome Atualizado",
            description: "The updated full name of the user."
        ),
        new OA\Property(
            property: "email",
            type: "string",
            format: "email",
            maxLength: 255,
            example: "updated.email@example.com",
            description: "The updated unique email address for the user."
        ),
        new OA\Property(
            property: "document",
            type: "string",
            maxLength: 14,
            example: "12345678901235",
            description: "The updated unique document number (CPF or CNPJ) for the user. Only digits."
        ),
        new OA\Property(
            property: "password",
            type: "string",
            format: "password",
            minLength: 8,
            nullable: true,
            example: "NewStrongP@ssw0rd",
            description: "The new password for the user. Optional. Must be at least 8 characters and confirmed if provided."
        ),
        new OA\Property(
            property: "password_confirmation",
            type: "string",
            format: "password",
            minLength: 8,
            nullable: true,
            example: "NewStrongP@ssw0rd",
            description: "Confirmation of the new password. Required if 'password' is provided and must match 'password'."
        ),
        new OA\Property(
            property: "user_type",
            type: "string",
            enum: ["common", "shopkeeper", "admin"],
            example: "common",
            description: "The updated type of user."
        )
    ],
    type: "object"
)]
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

        // Hasheia a senha SOMENTE SE ela for fornecida (nullable) e não for uma string vazia
        // A verificação `!empty($this->input('password'))` é crucial para evitar hashear uma string vazia
        // que pode vir de um campo de senha não preenchido no formulário HTML.
        if ($this->has('password') && !empty($this->input('password'))) {
            $this->merge([
                'password' => bcrypt($this->input('password')),
            ]);
        }
    }
}
