<?php

namespace App\Http\Controllers\Web; // Alterado o namespace para Web

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest; // Ainda pode usar o mesmo Request se a validação for igual
use App\Http\Requests\DepositRequest;   // Ainda pode usar o mesmo Request se a validação for igual
use App\Services\Transfer\TransferServiceInterface;
use App\Services\Deposit\DepositServiceInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse; // Retorno para redirecionamento após POST
use Illuminate\View\View; // Retorno para exibir a view
use Illuminate\Support\Facades\Log;
use Exception;
use App\Enums\TransactionStatus; // Importar TransactionStatus
use Illuminate\Support\Facades\Auth; // Para acessar o usuário autenticado
use App\Services\Transactions\RetrieveTransactionsInterface;

class TransactionController extends Controller
{
    protected TransferServiceInterface $transferService;
    protected DepositServiceInterface $depositService;
    protected UserRepositoryInterface $userRepository;
    protected RetrieveTransactionsInterface $retrieveTransactions;
    /**
     * Construtor do TransactionController.
     *
     * @param TransferServiceInterface $transferService
     * @param DepositServiceInterface $depositService
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        TransferServiceInterface $transferService,
        DepositServiceInterface $depositService,
        UserRepositoryInterface $userRepository,
        RetrieveTransactionsInterface $retrieveTransactions
    ) {
        $this->transferService = $transferService;
        $this->depositService = $depositService;
        $this->userRepository = $userRepository;
        $this->retrieveTransactions = $retrieveTransactions;
    }

    /**
     * Exibe a página principal de transações ou dashboard do usuário.
     */
    public function index(): View
    {
        $transactions = $this->retrieveTransactions->retrieve(10);
        return view('transactions.index', [
            'transactions' => $transactions,
        ]);
    }
    /**
     * Exibe o formulário de transferência.
     */
    public function showTransferForm(): View
    {
        $user = Auth::user();
        $users = $this->userRepository->getAllExcept((int)$user->id);
        return view('transactions.transfer-form', compact('users'));
    }

    /**
     * Realiza uma transferência de dinheiro e redireciona.
     *
     * @param TransferRequest $request
     * @return RedirectResponse
     */
    public function transfer(TransferRequest $request): RedirectResponse
    {
        $payer = $request->user();
        $payee = $this->userRepository->findById($request->payee_id);

        if (!$payee) {
            Log::error('Recebedor não encontrado para transferência via web.', ['payee_id' => $request->payee_id]);
            return back()->withInput()->withErrors(['transfer' => 'Erro: Recebedor não encontrado.']);
        }

        try {
            $transaction = $this->transferService->performTransfer(
                $payer,
                $payee,
                $request->amount
            );

            if ($transaction->status === TransactionStatus::COMPLETED) {
                return redirect()->route('transactions.index')->with('success', 'Transferência realizada com sucesso!');
            } elseif ($transaction->status === TransactionStatus::DENIED) {
                return back()->withInput()->withErrors(['transfer' => 'Transferência negada: ' . $transaction->reason]);
            } else {
                Log::error('Status de transação inesperado após transferência web.', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status->value
                ]);
                return back()->withInput()->withErrors(['transfer' => 'Ocorreu um erro inesperado ao processar a transferência.']);
            }
        } catch (Exception $e) {
            Log::error('Falha inesperada na transferência de dinheiro via web: ' . $e->getMessage(), [
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $request->amount,
                'exception' => $e
            ]);
            return back()->withInput()->withErrors(['transfer' => 'Erro ao realizar a transferência: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de depósito.
     */
    public function showDepositForm(): View
    {
        return view('transactions.deposit-form');
    }

    /**
     * Realiza um depósito de dinheiro para o usuário autenticado e redireciona.
     *
     * @param DepositRequest $request
     * @return RedirectResponse
     */
    public function deposit(DepositRequest $request): RedirectResponse
    {
        $user = $request->user();
        $amount = $request->amount;

        try {
            $this->depositService->performDeposit($user, $amount);

            return redirect()->route('transactions.index')->with('success', 'Depósito realizado com sucesso!');
        } catch (Exception $e) {
            Log::error('Falha no depósito de dinheiro via web: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
                'exception' => $e
            ]);
            return back()->withInput()->withErrors(['deposit' => 'Erro ao realizar o depósito: ' . $e->getMessage()]);
        }
    }
}
