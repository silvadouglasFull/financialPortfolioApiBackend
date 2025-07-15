<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Class LoginRequest
 *
 * Manipula as regras de validação para a requisição de login.
 */
#[OA\Schema(
    schema: "LoginRequest",
    title: "Login Request",
    description: "Data required for user login.",
    required: ["email", "password"],
    properties: [
        new OA\Property(
            property: "email",
            type: "string",
            format: "email",
            example: "user@example.com",
            description: "The user's email address."
        ),
        new OA\Property(
            property: "password",
            type: "string",
            format: "password",
            example: "password123",
            description: "The user's password."
        )
    ],
    type: "object"
)]
class LoginRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Neste caso, qualquer um pode tentar fazer login.
        // A lógica de autenticação real será no serviço/controlador.
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
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
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
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O e-mail fornecido não é válido.',
            'email.exists' => 'O e-mail fornecido não está cadastrado.',
            'password.required' => 'O campo senha é obrigatório.',
        ];
    }
}
