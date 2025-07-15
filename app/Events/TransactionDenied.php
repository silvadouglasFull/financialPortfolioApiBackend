<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TransactionDeniedEvent",
    title: "TransactionDeniedEvent",
    description: "Evento disparado quando uma transação é negada por regras de negócio ou validação.",
    properties: [
        new OA\Property(property: "transaction", ref: "#/components/schemas/Transaction", description: "A instância da transação que foi negada (já registrada como DENIED)."),
        new OA\Property(property: "payer", ref: "#/components/schemas/User", description: "O usuário que tentou realizar o pagamento (pagador)."),
        new OA\Property(property: "payee", ref: "#/components/schemas/User", description: "O usuário que seria o recebedor."),
        new OA\Property(property: "amount", type: "number", format: "float", example: 75.00, description: "O valor que foi tentado transferir."),
        new OA\Property(property: "reason", type: "string", example: "Insufficient balance", description: "O motivo da negação da transação.")
    ],
    type: "object"
)]
class TransactionDenied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * A instância da transação que foi negada (já registrada como DENIED).
     */
    public Transaction $transaction;

    /**
     * O usuário que tentou realizar o pagamento (pagador).
     */
    public User $payer;

    /**
     * O usuário que seria o recebedor.
     */
    public User $payee;

    /**
     * O valor que foi tentado transferir.
     */
    public float $amount;

    /**
     * O motivo da negação da transação.
     */
    public string $reason;

    /**
     * Cria uma nova instância do evento.
     *
     * @param Transaction $transaction A transação que foi registrada como DENIED.
     * @param User $payer O usuário que tentou pagar.
     * @param User $payee O usuário que seria o recebedor.
     * @param float $amount O valor da tentativa de transferência.
     * @param string $reason O motivo da negação.
     */
    public function __construct(Transaction $transaction, User $payer, User $payee, float $amount, string $reason)
    {
        $this->transaction = $transaction;
        $this->payer = $payer;
        $this->payee = $payee;
        $this->amount = $amount;
        $this->reason = $reason;
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
