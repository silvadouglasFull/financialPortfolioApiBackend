@extends('adminlte::page')

@section('title', 'Reverse Transaction')

@section('content_header')
    <h1>Reverse Transaction</h1>
@stop

@section('content')
    @auth
        @if (Auth::user()->user_type->value === \App\Enums\UserTypeEnum::ADMIN->value)
            <p>Hello, Administrator {{ Auth::user()->name }}!</p>

            {{-- Reversal Form (when an ID is passed) --}}
            @if (!empty($originalTransactionId))
                {{-- Card for Transaction Details --}}
                @if ($transactionToRevert) {{-- Only show if transaction was successfully retrieved --}}
                    <div class="card card-info"> {{-- Use card-info for info display --}}
                        <div class="card-header">
                            <h3 class="card-title">Original Transaction Details (#{{ $transactionToRevert->id }})</h3>
                        </div>
                        <x-transaction-info :transaction="$transactionToRevert" />
                    </div>
                @endif

                {{-- Card for Reversal Form --}}
                <div class="card card-success"> {{-- Changed to card-info to emphasize reversal action --}}
                    <div class="card-header">
                        <h3 class="card-title">Reversal Form</h3>
                    </div>
                    <form method="POST" action="{{ route('admin.reversals.store') }}">
                        @csrf
                        <div class="card-body">
                            {{-- Display validation errors --}}
                            @if ($errors->any())
                                <div class="alert alert-info">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Display success messages (flash messages) --}}
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="original_transaction_id">Original Transaction ID:</label>
                                <input type="text" name="original_transaction_id" id="original_transaction_id"
                                    class="form-control @error('original_transaction_id') is-invalid @enderror"
                                    value="{{ old('original_transaction_id', $originalTransactionId) }}" required readonly>
                                @error('original_transaction_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason for Reversal:</label>
                                <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
                                    required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Confirm Reversal
                            </button>
                            <a href="{{ route('admin.reversals.create') }}" class="btn btn-secondary float-right">
                                <i class="fas fa-list"></i> View All Transactions
                            </a>
                        </div>
                    </form>
                </div>
            @else
                {{-- List of Revertable Transactions (when no ID is passed) --}}
                @if ($transactions->isEmpty())
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="alert alert-info m-3">
                                No revertable transactions found.
                            </div>
                        </div>
                        {{-- The x-transaction-list component already includes pagination in its footer --}}
                    </div>
                @else
                    {{-- Using the x-transaction-list component for the table --}}
                    <x-transaction-list :transactions="$transactions" title="Revertable Transactions List" />
                @endif
            @endif
        @else
            {{-- Message for non-admin users --}}
            <div class="alert alert-info">
                You do not have permission to access this page.
            </div>
            <p><a href="{{ route('transactions.index') }}" class="btn btn-info">Return to Dashboard</a></p>
        @endauth
    @else
        {{-- Message for logged out users --}}
        <div class="alert alert-warning">
            You must be logged in to access this page. <a href="{{ route('login') }}">Login</a>
        </div>
    @endauth
@stop

@section('css')
    {{-- No additional CSS for this view --}}
@stop

@section('js')
    {{-- No additional JS for this view --}}
@stop
