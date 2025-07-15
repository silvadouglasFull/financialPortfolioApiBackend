<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReversalRequest;
use App\Services\Reversal\ReversalServiceInterface;
use App\Services\Transactions\RetrieveTransactionsInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Exceptions\UnauthorizedReversalException;

class ReversalController extends Controller
{
    protected ReversalServiceInterface $reversalService;
    protected RetrieveTransactionsInterface $retrieveTransactionsService; // Nova propriedade

    public function __construct(
        ReversalServiceInterface $reversalService,
        RetrieveTransactionsInterface $retrieveTransactionsService // Injetar o serviço
    ) {
        $this->reversalService = $reversalService;
        $this->retrieveTransactionsService = $retrieveTransactionsService; // Atribuir
    }

    /**
     * Exibe o formulário ou página para iniciar a reversão.
     * Se original_transaction_id não for fornecido, lista transações revertíveis.
     */
    public function showReversalForm(): View|RedirectResponse
    {
        $originalTransactionId = request('original_transaction_id');
        $transactions = null; // Initialize as null for the list
        $transactionToRevert = null; // Initialize as null for the specific transaction details

        // If a specific transaction ID is provided, fetch its details
        if (!empty($originalTransactionId)) {
            $transactionToRevert = $this->retrieveTransactionsService->getOriginalTransaction($originalTransactionId);

            // If the transaction is not found or not eligible for reversal, redirect back or show error
            if (!$transactionToRevert) {
                return redirect()->route('admin.reversals.create')->withErrors(['original_transaction_id' => 'Transaction not found or not eligible for reversal.']);
            }
        } else {
            // If no specific ID, get all revertable transactions for the list
            $transactions = $this->retrieveTransactionsService->getRevertableTransactions(10);
        }

        return view('admin.reversal.form', compact('originalTransactionId', 'transactions', 'transactionToRevert'));
    }

    /**
     * Lida com a requisição de reversão de uma transação via web.
     */
    public function reverse(ReversalRequest $request): RedirectResponse
    {
        // ... (o método reverse permanece o mesmo, pois ele só processa o POST)
        $user = $request->user();
        $originalTransactionId = $request->original_transaction_id;
        $reason = $request->reason;

        Log::info("Requisição de reversão via web recebida para transaction_id: {$originalTransactionId} por user_id: {$user->id}. Motivo: {$reason}");

        try {
            $this->reversalService->performReversal(
                $originalTransactionId,
                $user,
                $reason
            );

            return redirect()->route('transactions.index')->with('success', 'Reversal successful!'); // Melhor redirecionar para uma rota específica
        } catch (UnauthorizedReversalException $e) {
            Log::warning('Reversão negada por falta de autorização (Web): ' . $e->getMessage(), [
                'original_transaction_id' => $originalTransactionId,
                'reversed_by_user_id' => $user->id,
                'reason_input' => $reason,
                'error_message' => $e->getMessage(),
            ]);
            return back()->withInput()->withErrors(['reversal' => 'Você não tem permissão para realizar esta operação.']);
        } catch (Exception $e) {
            Log::error('Erro ao processar a reversão da transação (Web): ' . $e->getMessage(), [
                'original_transaction_id' => $originalTransactionId,
                'reversed_by_user_id' => $user->id,
                'reason_input' => $reason,
                'error_message' => $e->getMessage(),
                'exception' => $e
            ]);
            return back()->withInput()->withErrors(['reversal' => 'Falha ao realizar a reversão: ' . $e->getMessage()]);
        }
    }
}
