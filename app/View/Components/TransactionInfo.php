<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use App\Models\Transaction; // Importe o modelo Transaction

class TransactionInfo extends Component
{
    public Transaction $transaction; // Renomeado de $transactionToRevert para $transaction

    /**
     * Create a new component instance.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.transaction-info');
    }
}
