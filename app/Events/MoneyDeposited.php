<?php

namespace App\Events;

use App\Models\Transaction; // Importar a Model Transaction
use App\Models\User;        // Importar a Model User
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
