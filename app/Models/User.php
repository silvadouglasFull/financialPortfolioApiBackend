<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importar o trait HasUuids
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash; // Importar para Hash::make()
use Illuminate\Database\Eloquent\Casts\Attribute; // Importar para Accessors/Mutators no Laravel 9+

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids; // Incluir HasUuids aqui

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'document',
        'balance',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Mantido para compatibilidade, pode ser removido se não usar verificação de e-mail
        'password' => 'hashed', // Laravel 10+ automaticamente hasheia a senha ao atribuir
        'balance' => 'decimal:2', // Garante que o balance seja tratado como decimal com 2 casas
        'user_type' => \App\Enums\UserTypeEnum::class, // Cast para um Enum (se você criar um)
    ];

    /**
     * Define o tipo da chave primária como string (para UUID).
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indica que a chave primária não é um auto-incremento.
     *
     * @var bool
     */
    public $incrementing = false;

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Hash::make($value),
        );
    }
    // Relações, se houverem (serão adicionadas em etapas futuras, como transações)
    // public function transactions()
    // {
    //     return $this->hasMany(Transaction::class, 'payer_id')->orHasMany(Transaction::class, 'payee_id');
    // }
}
