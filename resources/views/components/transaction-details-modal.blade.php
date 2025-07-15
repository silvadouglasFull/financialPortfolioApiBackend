<div class="modal fade" id="transactionDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> {{-- modal-lg para modal maior --}}
        <div class="modal-content">
            <div class="modal-header bg-info"> {{-- Header azul para informação --}}
                <h5 class="modal-title" id="transactionDetailsModalLabel">Transaction Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- This is where the transaction info will be dynamically loaded --}}
                <div id="transactionModalBodyContent">
                    @isset($transaction)
                        <x-transaction-info :transaction="$transaction" />
                    @else
                        <p>No transaction details available.</p>
                    @endisset
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
