<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 * schema="UserTypeEnum",
 * title="UserTypeEnum",
 * description="Tipos de usuário permitidos no sistema",
 * type="string",
 * enum={"COMMON", "MERCHANT", "ADMIN"},
 * example="COMMON"
 * )
 */
enum UserTypeEnum: string
{
    // use EnumToArray; // Descomente se você criar e usar o trait EnumToArray

    case COMMON = 'COMMON';
    case MERCHANT = 'MERCHANT';
    case ADMIN = 'ADMIN';
    /**
     * Retorna uma descrição amigável para cada tipo de usuário.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::COMMON => 'Usuário comum',
            self::MERCHANT => 'Lojista ou Comerciante',
            self::ADMIN => 'Administrador do sistema', // Adicionado ADMIN
        };
    }

    /**
     * Retorna um array com todos os valores do enum.
     * Útil para validações ou listagens.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
