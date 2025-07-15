<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Illuminate\Support\Str; // Para UUID
use App\Enums\TransactionReversalStatus; // Vamos criar este Enum para o status da reversão
use OpenApi\Attributes as OA; // Import OpenApi Attributes

#[OA\Schema(
    schema: "TransactionReversal",
    title: "Transaction Reversal",
    description: "Represents a record of a transaction reversal, linking the original transaction to the new reversal transaction.",
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            format: "uuid",
            readOnly: true,
            example: "e1f2g3h4-i5j6-7890-1234-567890abcdef",
            description: "The unique identifier of the transaction reversal record."
        ),
        new OA\Property(
            property: "original_transaction_id",
            type: "string",
            format: "uuid",
            example: "a1b2c3d4-e5f6-7890-1234-567890abcdef",
            description: "The ID of the original transaction that was requested to be reversed."
        ),
        new OA\Property(
            property: "reversal_transaction_id",
            type: "string",
            format: "uuid",
            nullable: true,
            example: "z9y8x7w6-v5u4-3210-9876-543210fedcba",
            description: "The ID of the new transaction of type 'reversal' generated in the system. Null if the reversal failed or is pending."
        ),
        new OA\Property(
            property: "reversed_by_user_id",
            type: "string",
            format: "uuid",
            example: "user-admin-uuid",
            description: "The ID of the user (e.g., administrator) who initiated or approved this reversal request."
        ),
        new OA\Property(
            property: "reason",
            type: "string",
            example: "Customer requested cancellation within cooling-off period.",
            description: "The reason provided for the transaction reversal."
        ),
        new OA\Property(
            property: "status",
            type: "string",
            enum: ["pending", "completed", "failed", "rejected"], // Example values from TransactionReversalStatus Enum
            example: "completed",
            description: "The current status of the reversal request process."
        ),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T15:30:00.000000Z",
            description: "Timestamp when the reversal record was created."
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T15:35:00.000000Z",
            description: "Timestamp when the reversal record was last updated."
        )
    ],
    type: "object"
)]
class TransactionReversal extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'original_transaction_id',
        'reversal_transaction_id',
        'reversed_by_user_id',
        'reason',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'original_transaction_id' => 'string',
        'reversal_transaction_id' => 'string',
        'reversed_by_user_id' => 'string',
        'reason' => 'string',
        'status' => TransactionReversalStatus::class,
    ];

    /**
     * Define o relacionamento com a transação original que foi revertida.
     */
    public function originalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'original_transaction_id');
    }

    /**
     * Define o relacionamento com a nova transação do tipo REVERSAL gerada.
     */
    public function reversalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reversal_transaction_id');
    }

    /**
     * Define o relacionamento com o usuário que solicitou a reversão.
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }

    /**
     * Boot function for model.
     * Gera um UUID para o ID antes de criar um novo registro.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }
}
