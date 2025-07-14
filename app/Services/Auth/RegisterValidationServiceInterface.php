<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\RegisterRequest; // Pode ser um array de dados, ou um Request específico
use Illuminate\Validation\ValidationException; // Importar para lançar exceções de validação

/**
 * Interface RegisterValidationServiceInterface
 * Define o contrato para a validação de dados de registro de usuário.
 */
interface RegisterValidationServiceInterface
{
    /**
     * Valida os dados fornecidos para o registro de um novo usuário.
     *
     * @param array $data Os dados a serem validados (ex: do RegisterRequest).
     * @throws ValidationException Se a validação falhar.
     * @return array Os dados validados e filtrados.
     */
    public function validate(array $data): array;
}
