@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@endsection

@section('content')
@section('content')
    @auth
        <p>Welcome, {{ Auth::user()->name }}!</p>
        <div class="container">
            <div class="col-12 w-100">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign fa-lg"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">
                            <h4>Seu saldo atual</h4>
                        </span>
                        <span class="info-box-number">
                            <h1>R$ {{ number_format(Auth::user()->balance, 2, ',', '.') }}</h1>
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <h2>Latest transactions</h2>
            {{-- Aqui você esperaria receber as transações do controlador --}}
            <p>Transaction list will come here.</p>
            <x-transaction-list :transactions="$transactions" title="My Latest Transactions" />
        </div>
    @else
        <p>You are not logged in.</p>
        <p><a href="{{ route('login') }}">Do Login</a> ou <a href="{{ route('register') }}">Register</a></p>
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
@endsection
