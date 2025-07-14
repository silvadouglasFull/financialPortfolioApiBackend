<?php

namespace App\Events;

use App\Models\Transaction; // Importar a Model Transaction
use App\Models\User;        // Importar a Model User
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
