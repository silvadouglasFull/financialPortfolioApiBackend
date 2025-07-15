<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "MoneyTransferredEvent",
    title: "MoneyTransferredEvent",
    description: "Evento disparado após uma transferência de dinheiro bem-sucedida.",
    properties: [
        new OA\Property(property: "transaction", ref: "#/components/schemas/Transaction", description: "A instância da transação concluída."),
        new OA\Property(property: "payer", ref: "#/components/schemas/User", description: "O usuário pagador."),
        new OA\Property(property: "payee", ref: "#/components/schemas/User", description: "O usuário recebedor."),
        new OA\Property(property: "amount", type: "number", format: "float", example: 50.00, description: "O valor transferido.")
    ],
    type: "object"
)]
class MoneyTransferred
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * A instância da transação concluída.
     */
    public Transaction $transaction;

    /**
     * O usuário pagador.
     */
    public User $payer;

    /**
     * O usuário recebedor.
     */
    public User $payee;

    /**
     * O valor transferido.
     */
    public float $amount;

    /**
     * Cria uma nova instância do evento.
     */
    public function __construct(Transaction $transaction, User $payer, User $payee, float $amount)
    {
        $this->transaction = $transaction;
        $this->payer = $payer;
        $this->payee = $payee;
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
