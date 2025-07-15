<?php

namespace App\Enums;

enum TransactionReversalStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REVERSED = 'REVERSED';
    case DENIED = 'DENIED';
}
