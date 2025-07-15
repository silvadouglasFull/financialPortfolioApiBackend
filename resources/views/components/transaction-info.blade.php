@if (isset($transaction->amount))
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Amount:</strong> R$
                    {{ number_format($transaction->amount, 2, ',', '.') }}</p>
                <p><strong>Type:</strong> {{ $transaction->type->value }}</p>
                <p><strong>Status:</strong> <span
                        class="badge {{ $transaction->status === \App\Enums\TransactionStatus::COMPLETED ? 'bg-success' : 'bg-warning' }}">
                        {{ $transaction->status->value }}</span></p>
                <p><strong>Description:</strong> {{ $transaction->description ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Payer:</strong>
                    {{ $transaction->payer ? $transaction->payer->name . ' (' . $transaction->payer->email . ')' : 'N/A (Deposit)' }}
                </p>
                <p><strong>Payee:</strong>
                    {{ $transaction->payee ? $transaction->payee->name . ' (' . $transaction->payee->email . ')' : 'N/A (Reversal)' }}
                </p>
                <p><strong>Date:</strong> {{ $transaction->created_at->format('Y/m/d H:i:s') }}</p>
                <p><strong>Updated:</strong> {{ $transaction->updated_at->format('Y/m/d H:i:s') }}
                </p>
            </div>
        </div>
    </div>
@endif
