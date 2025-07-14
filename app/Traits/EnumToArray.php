<?php

// app/Traits/EnumToArray.php

namespace App\Traits;

trait EnumToArray
{
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function toAssociativeArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->name] = $case->value;
            return $carry;
        }, []);
    }
}
