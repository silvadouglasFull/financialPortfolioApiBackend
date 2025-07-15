<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TransactionStatus",
    type: "string",
    enum: ["PENDING", "COMPLETED", "FAILED", "REVERSED", "DENIED"],
    example: "PENDING"
)]
// PHP 8.1+ Native Enum
enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REVERSED = 'REVERSED';
    case DENIED = 'DENIED';
}
