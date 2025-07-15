<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Exibe a página principal de transações ou dashboard do usuário.
     */
    public function index(): View
    {
        return view('transactions.index');
    }
}
