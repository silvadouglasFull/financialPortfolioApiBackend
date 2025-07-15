<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory; // Mantenha esta linha para o Factory!

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

    // ... (Método boot e relacionamentos)

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
