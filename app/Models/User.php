<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Casts\Attribute;
use OpenApi\Attributes as OA;

/**
 * App\Models\User
 *
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'])
 */
#[OA\Schema(
    schema: "User",
    title: "User",
    description: "Represents a user in the system, either a common user, a shopkeeper, or an administrator.",
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            format: "uuid",
            readOnly: true,
            example: "b1c2d3e4-f5a6-7890-1234-567890abcdef",
            description: "The unique identifier of the user."
        ),
        new OA\Property(
            property: "name",
            type: "string",
            example: "Maria Santos",
            description: "The full name of the user."
        ),
        new OA\Property(
            property: "email",
            type: "string",
            format: "email",
            uniqueItems: true,
            example: "maria.santos@example.com",
            description: "The unique email address of the user."
        ),
        new OA\Property(
            property: "document",
            type: "string",
            uniqueItems: true,
            example: "12345678900",
            description: "The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits."
        ),
        new OA\Property(
            property: "balance",
            type: "number",
            format: "float",
            example: 1250.75,
            description: "The current monetary balance of the user, with 2 decimal places."
        ),
        new OA\Property(
            property: "user_type",
            type: "string",
            enum: ["common", "shopkeeper", "admin"], // Based on your UserTypeEnum
            example: "common",
            description: "The type of user."
        ),
        new OA\Property(
            property: "google_id",
            type: "string",
            nullable: true,
            example: "108347892347923487",
            description: "The Google ID if the user registered via Google OAuth."
        ),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T10:00:00.000000Z",
            description: "Timestamp when the user account was created."
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time",
            readOnly: true,
            example: "2024-07-15T10:05:00.000000Z",
            description: "Timestamp when the user account was last updated."
        )
    ],
    type: "object"
)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

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
        'google_id'
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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'user_type' => \App\Enums\UserTypeEnum::class,
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

    /**
     * Hash the user's password when it is set.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
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
