<?php

namespace App\Services\Auth;

class SetJWTCookie
{
    public static function setCookie(array $result): \Symfony\Component\HttpFoundation\Cookie
    {
        $cookie = cookie(
            'token',
            $result['token'],
            60, // Expiração em minutos
            null,
            null,
            true, // Secure
            true, // HttpOnly
            false,
            'Strict'
        );
        return $cookie;
    }
}
