<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Illuminate\Support\Str; // Para UUID
use App\Enums\TransactionReversalStatus; // Vamos criar este Enum para o status da reversão

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
        // O status da reversão em si. Se usar um Enum específico, como TransactionReversalStatus:
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
