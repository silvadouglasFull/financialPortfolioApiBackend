<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UnauthorizedReversalError",
    type: "object",
    description: "Returned when a user attempts to reverse a transaction they are not authorized to reverse.",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Você não tem permissão para reverter esta transação."),
        new OA\Property(property: "error", type: "string", example: "Reversão não autorizada.")
    ]
)]
class UnauthorizedReversalError {}
