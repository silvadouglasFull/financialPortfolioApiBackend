<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TransactionResponse",
    type: "object",
    description: "Schema representing a transaction, including details about sender and recipient.",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "d3f4e5c6-7890-1234-5678-abcdefabcdef"),
        new OA\Property(property: "payer_id", type: "string", format: "uuid", example: "a1b2c3d4-5678-90ef-1234-567890abcdef"),
        new OA\Property(property: "payee_id", type: "string", format: "uuid", example: "b2c3d4e5-6789-01ab-2345-678901abcdef"),
        new OA\Property(property: "amount", type: "string", format: "decimal", example: "150.00"),
        new OA\Property(property: "type", type: "string", example: "TRANSFER"),
        new OA\Property(property: "status", type: "string", example: "REVERSED"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-06-01T10:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-06-02T15:00:00Z")
    ]
)]
class TransactionResponse {}
