@extends('adminlte::page')

@section('title', 'New Transfer')

@section('content_header')
    <h1>Make New Transfer</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transfer Data</h3>
        </div>
        <form method="POST" action="{{ route('transactions.transfer.store') }}">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="payee_id">Receiver:</label>
                    <select name="payee_id" id="payee_id" class="form-control @error('payee_id') is-invalid @enderror"
                        required>
                        <option value="">Select a recipient</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('payee_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('payee_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="amount">Value:</label>
                    <input type="number" name="amount" id="amount"
                        class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                        step="0.01" min="0.01" required>
                    @error('amount')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info">Transferir</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    {{-- Nenhum CSS adicional por enquanto --}}
@stop

@section('js')
    {{-- Nenhum JS adicional por enquanto --}}
@stop
