<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3> {{-- Usa a propriedade title --}}

        <div class="card-tools">
            {{-- A paginação pode ser mais complexa aqui, geralmente requer links do Laravel ou Livewire --}}
            <ul class="pagination pagination-sm float-right">
                {{ $transactions->links() }}
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>ID</th> {{-- Adicionando ID para referência --}}
                    <th>Payer / Payee</th> {{-- Quem pagou / recebeu --}}
                    <th>Amount</th>
                    <th>Type</th>
                    <th style="width: 40px">Status</th>
                </tr>
            </thead>
            <tbody>
                {{-- Itera sobre as transações passadas para o componente --}}
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}.</td> {{-- Número da linha --}}
                        <td>{{ $transaction->id }}</td>
                        <td>
                            @if ($transaction->payer)
                                {{ $transaction->payer->name }} (Payer)
                            @else
                                N/A (Deposit)
                            @endif
                            ->
                            @if ($transaction->payee)
                                {{ $transaction->payee->name }} (Payee)
                            @else
                                N/A (Reversal)
                            @endif
                        </td>
                        <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                        <td>{{ $transaction->type->value }}</td> {{-- Acessa o valor do Enum --}}
                        <td>
                            <span
                                class="badge {{ $transaction->status === \App\Enums\TransactionStatus::COMPLETED ? 'bg-success' : 'bg-warning' }}">
                                {{ $transaction->status->value }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
