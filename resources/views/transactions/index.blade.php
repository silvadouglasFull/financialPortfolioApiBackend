@extends('adminlte::page')

@section('title', 'Minhas Transações')

@section('content_header')
    <h1>Transactions</h1>
@stop

@section('content')
    @auth
        <x-transaction-list :transactions="$transactions" title="My Latest Transactions" />

        <button type="submit" class="btn btn-info mt-3">Create New</button>
    @else
        <p>Você não está logado.</p>
        <p><a href="{{ route('login') }}">Fazer Login</a> ou <a href="{{ route('register') }}">Cadastrar-se</a></p>
    @endauth

    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif
@stop

@section('js')
    {{-- Scripts específicos da página --}}
@stop
