<?php
// app/Swagger/Schemas/UserResponse.php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserResponse",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "a1b2c3d4-5678-90ef-1234-567890abcdef"),
        new OA\Property(property: "name", type: "string", example: "João da Silva"),
        new OA\Property(property: "email", type: "string", format: "email", example: "joao@example.com"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T12:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-02T12:00:00Z"),
    ],
    type: "object"
)]
class UserResponse {}
