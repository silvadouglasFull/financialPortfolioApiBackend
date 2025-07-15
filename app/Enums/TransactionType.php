<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TransactionType",
    type: "string",
    enum: ["TRANSFER", "DEPOSIT", "REVERSAL"],
    example: "TRANSFER"
)]
// PHP 8.1+ Native Enum
enum TransactionType: string
{
    case TRANSFER = 'TRANSFER';
    case DEPOSIT = 'DEPOSIT';
    case REVERSAL = 'REVERSAL';
}
