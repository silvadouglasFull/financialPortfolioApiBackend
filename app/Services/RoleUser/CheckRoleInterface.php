<?php

namespace App\Services\RoleUser;

interface CheckRoleInterface
{
    public function check(string $role): bool;
}
