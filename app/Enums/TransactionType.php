<?php

namespace App\Enums;

// PHP 8.1+ Native Enum
enum TransactionType: string
{
    case TRANSFER = 'TRANSFER';
    case DEPOSIT = 'DEPOSIT';
    case REVERSAL = 'REVERSAL';
}
