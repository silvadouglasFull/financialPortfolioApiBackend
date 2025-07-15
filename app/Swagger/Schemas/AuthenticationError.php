<?php
// app/Swagger/Schemas/AuthenticationError.php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthenticationError",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Falha na autenticação."),
        new OA\Property(property: "error", type: "string", example: "Credenciais inválidas."),
    ],
    type: "object"
)]
class AuthenticationError {}
