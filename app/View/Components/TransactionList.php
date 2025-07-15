<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionList extends Component
{
    public LengthAwarePaginator $transactions;
    public string $title; // Adicione um título configurável

    /**
     * Cria uma nova instância do componente.
     */
    public function __construct(LengthAwarePaginator $transactions, string $title = 'Transactions')
    {
        $this->transactions = $transactions;
        $this->title = $title;
    }

    /**
     * Obtém a view/conteúdo que representa o componente.
     */
    public function render(): View
    {
        return view('components.transaction-list');
    }
}
