<?php

namespace App\Http\Controllers;

use App\Services\Transactions\RetrieveTransactionsInterface;

class HomeController extends Controller
{
    protected RetrieveTransactionsInterface $retrieveTransactions;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(RetrieveTransactionsInterface $retrieveTransactions)
    {
        $this->middleware('auth');
        $this->retrieveTransactions = $retrieveTransactions;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        $transactions = $this->retrieveTransactions->retrieve(10);
        return view('home', [
            'transactions' => $transactions,
        ]);
    }
}
