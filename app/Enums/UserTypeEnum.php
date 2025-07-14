<?php

namespace App\Enums;

use App\Traits\EnumToArray; // Opcional: Adicionar um trait para converter Enum para array/lista

/**
 * @OA\Schema(
 * title="UserTypeEnum",
 * description="Tipos de usuário permitidos no sistema",
 * type="string",
 * enum={"COMMON", "MERCHANT"}
 * )
 */
enum UserTypeEnum: string
{
    // use EnumToArray; // Descomente se você criar e usar o trait EnumToArray

    case COMMON = 'COMMON';
    case MERCHANT = 'MERCHANT';

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
