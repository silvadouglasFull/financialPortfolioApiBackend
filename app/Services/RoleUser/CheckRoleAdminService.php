<?php

namespace App\Services\RoleUser;


class CheckRoleAdminService implements CheckRoleInterface
{
    public function check(string $role): bool
    {
        return (strtolower($role) === "admin");
    }
}
