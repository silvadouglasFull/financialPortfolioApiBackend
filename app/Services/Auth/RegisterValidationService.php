<?php

namespace App\Services\Auth;

use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Classe RegisterValidationService
 * Responsável por validar os dados de entrada para o registro de um novo usuário.
 */
class RegisterValidationService implements RegisterValidationServiceInterface
{
    /**
     * Valida os dados fornecidos para o registro de um novo usuário.
     *
     * @param array $data Os dados a serem validados.
     * @throws ValidationException Se a validação falhar.
     * @return array Os dados validados e filtrados.
     */
    public function validate(array $data): array
    {
        // Define as regras de validação para cada campo
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'document' => ['required', 'string', 'unique:users,document'], // Validação de CPF/CNPJ será customizada
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' requer 'password_confirmation'
            'user_type' => ['required', 'string', 'in:' . implode(',', UserTypeEnum::values())], // Valida contra os valores do Enum
        ];

        // Define mensagens de validação personalizadas (opcional, mas boa prática)
        $messages = [
            'name.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O e-mail fornecido não é válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'document.required' => 'O campo documento é obrigatório.',
            'document.unique' => 'Este documento (CPF/CNPJ) já está cadastrado.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',
            'password.confirmed' => 'A confirmação de senha não corresponde.',
            'user_type.required' => 'O campo tipo de usuário é obrigatório.',
            'user_type.in' => 'O tipo de usuário fornecido não é válido.',
        ];

        // Cria o validador com os dados, regras e mensagens
        $validator = Validator::make($data, $rules, $messages);

        // Adiciona validação customizada para CPF/CNPJ e senha forte
        $validator->after(function ($validator) use ($data) {
            // Validação de CPF/CNPJ
            if (isset($data['document']) && !$this->isValidCpfCnpj($data['document'])) {
                $validator->errors()->add('document', 'O documento (CPF/CNPJ) fornecido não é válido.');
            }

            // Validação de senha forte
            if (isset($data['password']) && !$this->isStrongPassword($data['password'])) {
                $validator->errors()->add('password', 'A senha é muito fraca. Ela deve conter letras maiúsculas, minúsculas, números e símbolos.');
            }
        });

        // Se a validação falhar, lança uma ValidationException
        $validator->validate();

        // Retorna os dados validados e filtrados (o Laravel Validator automaticamente remove dados não definidos nas regras)
        return $validator->validated();
    }

    /**
     * Valida um CPF ou CNPJ.
     *
     * @param string $document O número do documento.
     * @return bool
     */
    private function isValidCpfCnpj(string $document): bool
    {
        // Remove caracteres não numéricos
        $document = preg_replace('/[^0-9]/', '', $document);

        if (strlen($document) === 11) {
            // Validação de CPF
            return $this->isValidCpf($document);
        } elseif (strlen($document) === 14) {
            // Validação de CNPJ
            return $this->isValidCnpj($document);
        }

        return false;
    }

    /**
     * Valida um CPF.
     *
     * @param string $cpf
     * @return bool
     */
    private function isValidCpf(string $cpf): bool
    {
        // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula o segundo dígito verificador
        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        // Verifica se os dígitos verificadores calculados correspondem aos do CPF
        return ($cpf[9] == $digito1) && ($cpf[10] == $digito2);
    }

    /**
     * Valida um CNPJ.
     *
     * @param string $cnpj
     * @return bool
     */
    private function isValidCnpj(string $cnpj): bool
    {
        // Verifica se todos os dígitos são iguais (ex: 00.000.000/0000-00)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validação de CNPJ (simplificada, em ambiente de produção usar biblioteca)
        $b = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        for ($i = 0, $j = 0, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $b[++$j];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        for ($i = 0, $j = 0, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $b[$j++];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return ($cnpj[12] == $digito1) && ($cnpj[13] == $digito2);
    }

    /**
     * Valida se a senha é "forte".
     * Requer: mínimo 8 caracteres, maiúscula, minúscula, número, símbolo.
     *
     * @param string $password
     * @return bool
     */
    private function isStrongPassword(string $password): bool
    {
        // Mínimo de 8 caracteres
        if (strlen($password) < 8) {
            return false;
        }

        // Pelo menos uma letra maiúscula
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Pelo menos uma letra minúscula
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Pelo menos um número
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Pelo menos um caractere especial (símbolo)
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        // Verifica se a senha não é uma sequência ou repetição óbvia (ex: 12345678, abcdefgh, qwertzy)
        // Isso é uma verificação básica, não exaustiva
        $passwordLower = strtolower($password);
        if (
            str_contains('123456789', $passwordLower) ||
            str_contains('987654321', $passwordLower) ||
            str_contains('abcdefghi', $passwordLower) ||
            str_contains('zyxwvwxyz', $passwordLower) ||
            preg_match('/(.)\1{2,}/', $passwordLower) // Repetição de 3 ou mais caracteres (ex: aaa)
        ) {
            return false;
        }

        return true;
    }
}
