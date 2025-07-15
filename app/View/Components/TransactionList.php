<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth; // Importar Auth para verificar o usuário
use App\Enums\UserTypeEnum; // Importar seu Enum de tipos de usuário

class TransactionList extends Component
{
    public LengthAwarePaginator $transactions;
    public string $title;
    public bool $isAdmin; // Nova propriedade para indicar se o usuário é admin

    /**
     * Cria uma nova instância do componente.
     */
    public function __construct(LengthAwarePaginator $transactions, string $title = 'Transactions')
    {
        $this->transactions = $transactions;
        $this->title = $title;
        // Verifica se o usuário autenticado é um administrador
        // Certifique-se de que o campo 'user_type' no seu modelo User está configurado
        // e que App\Enums\UserTypeEnum::ADMIN está correto.
        $this->isAdmin = Auth::check() && Auth::user()->user_type === UserTypeEnum::ADMIN;
    }

    /**
     * Obtém a view/conteúdo que representa o componente.
     */
    public function render(): View
    {
        return view('components.transaction-list');
    }
}
