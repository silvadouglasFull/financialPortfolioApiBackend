<?php

namespace App\Services\RoleUser;

use InvalidArgumentException;

class CheckRoleFactory
{
    public static function make(string $requiredRole): CheckRoleInterface
    {
        return match (strtolower($requiredRole)) {
            'admin'   => new CheckRoleAdminService(),
            'manager' => new CheckRoleManagerService(),
            default   => throw new InvalidArgumentException("Role strategy not found: {$requiredRole}")
        };
    }
}
