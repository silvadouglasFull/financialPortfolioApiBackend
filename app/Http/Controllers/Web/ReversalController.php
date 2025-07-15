<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReversalRequest;
use App\Services\Reversal\ReversalServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Exceptions\UnauthorizedReversalException;

class ReversalController extends Controller
{
    protected ReversalServiceInterface $reversalService;

    public function __construct(ReversalServiceInterface $reversalService)
    {
        $this->reversalService = $reversalService;
    }

    /**
     * Exibe o formulário ou página para iniciar a reversão.
     */
    public function showReversalForm(): View
    {
        // Você pode passar dados aqui, como uma lista de transações para o admin selecionar
        return view('admin.reversal.form');
    }

    /**
     * Lida com a requisição de reversão de uma transação via web.
     */
    public function reverse(ReversalRequest $request): RedirectResponse
    {
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

            return redirect()->back()->with('success', 'Reversão realizada com sucesso!');
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
