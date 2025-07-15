<?php
// app/Swagger/Schemas/ValidationError.php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ValidationError",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Erro de validação."),
        new OA\Property(
            property: "errors",
            type: "object",
            additionalProperties: new OA\AdditionalProperties(
                type: "array",
                items: new OA\Items(type: "string")
            )
        ),
    ],
    type: "object"
)]
class ValidationError {}
