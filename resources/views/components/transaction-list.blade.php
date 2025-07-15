<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>

        <div class="card-tools">
            <ul class="pagination pagination-sm float-right">
                {{ $transactions->links() }}
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover"> {{-- Added table-hover for better UX --}}
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Payer</th>
                    <th>Payee</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th style="width: 40px">Status</th>
                    @if ($isAdmin)
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr class="transaction-row" data-transaction-id="{{ $transaction->id }}"
                        data-amount="{{ number_format($transaction->amount, 2, ',', '.') }}"
                        data-type="{{ $transaction->type->value }}" data-status="{{ $transaction->status->value }}"
                        data-description="{{ $transaction->description ?? 'N/A' }}"
                        data-payer-name="{{ $transaction->payer ? $transaction->payer->name : 'N/A (Deposit)' }}"
                        data-payer-email="{{ $transaction->payer ? $transaction->payer->email : '' }}"
                        data-payee-name="{{ $transaction->payee ? $transaction->payee->name : 'N/A (Reversal)' }}"
                        data-payee-email="{{ $transaction->payee ? $transaction->payee->email : '' }}"
                        data-created-at="{{ $transaction->created_at->format('Y/m/d H:i:s') }}"
                        data-updated-at="{{ $transaction->updated_at->format('Y/m/d H:i:s') }}"
                        data-status-class="{{ $transaction->status === \App\Enums\TransactionStatus::COMPLETED ? 'bg-success' : ($transaction->status === \App\Enums\TransactionStatus::PENDING ? 'bg-warning' : 'bg-danger') }}"
                        style="cursor: pointer">
                        <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}.
                        </td>
                        <td>
                            @if ($transaction->payer)
                                {{ $transaction->payer->name }}
                            @else
                                N/A (Deposit)
                            @endif
                        </td>
                        <td>
                            @if ($transaction->payee)
                                {{ $transaction->payee->name }} (Payee)
                            @else
                                N/A (Reversal)
                            @endif
                        </td>
                        <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                        <td>{{ $transaction->type->value }}</td>
                        <td>
                            <span
                                class="badge {{ $transaction->status === \App\Enums\TransactionStatus::COMPLETED ? 'bg-success' : ($transaction->status === \App\Enums\TransactionStatus::PENDING ? 'bg-warning' : 'bg-danger') }}">
                                {{ $transaction->status->value }}
                            </span>
                        </td>
                        @if ($isAdmin)
                            <td>
                                @if (
                                    $transaction->status === \App\Enums\TransactionStatus::COMPLETED &&
                                        $transaction->type !== \App\Enums\TransactionType::REVERSAL)
                                    <a href="{{ route('admin.reversals.create', ['original_transaction_id' => $transaction->id]) }}"
                                        class="btn btn-sm btn-info" title="Revert Transaction">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? '7' : '6' }}">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $transactions->links() }}
    </div>
</div>

{{-- Inclua a modal no final do componente list --}}
<x-transaction-details-modal />

@push('js')
    <script>
        $(document).ready(function() {
            $('.transaction-row').on('click', function() {
                var transactionData = $(this).data(); // Get all data- attributes

                // Construct the HTML for transaction details using the data attributes
                var detailsHtml = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Amount:</strong> R$ ${transactionData.amount}</p>
                        <p><strong>Type:</strong> ${transactionData.type}</p>
                        <p><strong>Status:</strong> <span class="badge ${transactionData.statusClass}">${transactionData.status}</span></p>
                        <p><strong>Description:</strong> ${transactionData.description}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Payer:</strong> ${transactionData.payerName} ${transactionData.payerEmail ? '(' + transactionData.payerEmail + ')' : ''}</p>
                        <p><strong>Payee:</strong> ${transactionData.payeeName} ${transactionData.payeeEmail ? '(' + transactionData.payeeEmail + ')' : ''}</p>
                        <p><strong>Date:</strong> ${transactionData.createdAt}</p>
                        <p><strong>Updated:</strong> ${transactionData.updatedAt}</p>
                    </div>
                </div>
            </div>
        `;

                // Update modal content and show
                $('#transactionModalBodyContent').html(detailsHtml);
                $('#transactionDetailsModalLabel').text('Transaction Details #' + transactionData
                    .transactionId);
                $('#transactionDetailsModal').modal('show');
            });
        });
    </script>
@endpush
