<?php

namespace App\Events;

use App\Models\Transaction; // Importar a Model Transaction
use App\Models\TransactionReversal; // Importar a Model TransactionReversal
use App\Models\User; // Importar a Model User (para os usuários envolvidos na transação original)
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionReversed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * A instância da transação original que foi revertida.
     */
    public Transaction $originalTransaction;

    /**
     * A instância da nova transação do tipo REVERSAL que efetivou a reversão.
     */
    public Transaction $reversalTransaction;

    /**
     * A instância do registro de reversão.
     */
    public TransactionReversal $transactionReversalRecord;

    /**
     * O usuário pagador da transação original (pode ser null para depósitos).
     */
    public ?User $payer;

    /**
     * O usuário recebedor da transação original.
     */
    public User $payee;

    /**
     * O valor que foi revertido.
     */
    public float $amount;

    /**
     * O motivo da reversão.
     */
    public string $reason;

    /**
     * Cria uma nova instância do evento.
     *
     * @param Transaction $originalTransaction A transação original que foi revertida.
     * @param Transaction $reversalTransaction A nova transação do tipo REVERSAL criada.
     * @param TransactionReversal $transactionReversalRecord O registro da reversão na tabela transaction_reversals.
     * @param User|null $payer O usuário pagador da transação original (null se depósito).
     * @param User $payee O usuário recebedor da transação original.
     * @param float $amount O valor da transação revertida.
     * @param string $reason O motivo da reversão.
     */
    public function __construct(
        Transaction $originalTransaction,
        Transaction $reversalTransaction,
        TransactionReversal $transactionReversalRecord,
        ?User $payer, // Pode ser nulo para depósitos
        User $payee,
        float $amount,
        string $reason
    ) {
        $this->originalTransaction = $originalTransaction;
        $this->reversalTransaction = $reversalTransaction;
        $this->transactionReversalRecord = $transactionReversalRecord;
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
