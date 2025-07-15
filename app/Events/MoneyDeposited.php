<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpenApi\Attributes as OA; // Adicione esta linha para importar as anotações

#[OA\Schema(
    schema: "MoneyDepositedEvent",
    title: "MoneyDepositedEvent",
    description: "Evento disparado após um depósito de dinheiro bem-sucedido.",
    properties: [
        new OA\Property(property: "transaction", ref: "#/components/schemas/Transaction", description: "A transação de depósito concluída."),
        new OA\Property(property: "user", ref: "#/components/schemas/User", description: "O usuário para quem o depósito foi feito."),
        new OA\Property(property: "amount", type: "number", format: "float", example: 100.50, description: "O valor depositado.")
    ],
    type: "object"
)]
class MoneyDeposited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * A instância da transação de depósito concluída.
     */
    public Transaction $transaction;

    /**
     * O usuário para quem o depósito foi feito.
     */
    public User $user;

    /**
     * O valor depositado.
     */
    public float $amount;

    /**
     * Cria uma nova instância do evento.
     *
     * @param Transaction $transaction A transação de depósito criada.
     * @param User $user O usuário que recebeu o depósito.
     * @param float $amount O valor depositado.
     */
    public function __construct(Transaction $transaction, User $user, float $amount)
    {
        $this->transaction = $transaction;
        $this->user = $user;
        $this->amount = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /*
     * Não é necessário para este caso, a menos que você queira transmitir via WebSockets.
     * public function broadcastOn(): array
     * {
     * return [];
     * }
     */
}
