<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Transaction",
    title: "Transaction",
    description: "Represents a financial transaction within the system.",
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            format: "uuid",
            readOnly: true,
            example: "a1b2c3d4-e5f6-7890-1234-567890abcdef",
            description: "The unique identifier of the transaction."
        ),
        new OA\Property(
            property: "payer_id",
            type: "string",
            format: "uuid",
            nullable: true,
            example: "user-uuid-1",
            description: "The ID of the user who initiated the transaction (payer). Null for deposits."
        ),
        new OA\Property(
            property: "payee_id",
            type: "string",
            format: "uuid",
            example: "user-uuid-2",
            description: "The ID of the user who received the transaction (payee)."
        ),
        new OA\Property(
            property: "amount",
            type: "number",
            format: "float",
            example: 150.75,
            description: "The amount of money involved in the transaction."
        ),
        new OA\Property(
            property: "status",
            type: "string",
            enum: ["pending", "completed", "denied", "reversed"], // Based on your TransactionStatus Enum
            example: "completed",
            description: "The current status of the transaction."
        ),
        new OA\Property(
            property: "type",
            type: "string",
            enum: ["transfer", "deposit", "reversal"], // Based on your TransactionType Enum
            example: "transfer",
            description: "The type of the transaction."
        ),
        new OA\Property(
            property: "reverted_from",
            type: "string",
            format: "uuid",
            nullable: true,
            example: "original-transaction-uuid",
            description: "The ID of the original transaction that this transaction reversed (if type is reversal)."
        ),
        new OA\Property(
            property: "reason",
            type: "string",
            nullable: true,
            example: "Insufficient funds",
            description: "The reason for the transaction status (e.g., denial reason, reversal reason)."
        ),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T10:00:00.000000Z",
            description: "Timestamp when the transaction was created."
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T10:05:00.000000Z",
            description: "Timestamp when the transaction was last updated."
        )
    ],
    type: "object"
)]
class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'payer_id',
        'payee_id',
        'amount',
        'status',
        'type',
        'reverted_from',
        'reason',
    ];

    protected $casts = [
        'id' => 'string',
        'payer_id' => 'string',
        'payee_id' => 'string',
        'amount' => 'float',
        'status' => TransactionStatus::class,
        'type' => TransactionType::class,
        'reverted_from' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
