<?php

namespace App\Enums;

// PHP 8.1+ Native Enum
enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case REVERSED = 'REVERSED';
}
