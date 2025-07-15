<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TransactionStatus; // Importar o Enum TransactionStatus
use App\Enums\TransactionType;
use Illuminate\Support\Str; // Para gerar UUID no creating

/**
 * Class Transaction
 *
 * @property string $id
 * @property string $payer_id
 * @property string $payee_id
 * @property float $amount
 * @property TransactionStatus $status
 * @property string|null $reverted_from
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $payer
 * @property-read User $payee
 * @property-read Transaction|null $originalTransaction
 */
class Transaction extends Model
{
    use HasFactory;

    // A chave primária é um UUID, não um inteiro auto-incrementável
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', // ID deve ser fillable se gerado manualmente ou via observador
        'payer_id',
        'payee_id',
        'amount',
        'status',
        'reverted_from',
        'type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string', // Garante que UUIDs são tratados como strings
        'payer_id' => 'string',
        'payee_id' => 'string',
        'amount' => 'decimal:2', // Converte automaticamente para float com 2 casas decimais
        'status' => TransactionStatus::class, // Usa o Enum para o campo status
        'reverted_from' => 'string',
        'type' => TransactionType::class
    ];

    /**
     * Define o relacionamento com o usuário pagador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /**
     * Define o relacionamento com o usuário recebedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    /**
     * Define o relacionamento com a transação original que esta reverteu (se aplicável).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originalTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reverted_from');
    }

    /**
     * Boot the model.
     * Garante que um UUID seja gerado automaticamente ao criar uma nova transação.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }
}
