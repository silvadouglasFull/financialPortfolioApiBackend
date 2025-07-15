<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TransactionReversalStatus",
    type: "string",
    enum: ["PENDING", "COMPLETED", "FAILED", "REVERSED", "DENIED"],
    example: "PENDING"
)]
enum TransactionReversalStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REVERSED = 'REVERSED';
    case DENIED = 'DENIED';
}
