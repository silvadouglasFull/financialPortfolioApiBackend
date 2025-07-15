<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Transações</title>
</head>

<body>
    <h1>Dashboard de Transações</h1>

    @auth
        <p>Bem-vindo, {{ Auth::user()->name }}!</p>
        <p>Seu saldo atual: R$ {{ number_format(Auth::user()->balance, 2, ',', '.') }}</p>

        <h2>Últimas Transações</h2>
        {{-- Aqui você esperaria receber as transações do controlador --}}
        {{-- Como o controlador `TransactionController@index` não passa dados ainda, este será um placeholder --}}
        <p>Lista de transações virá aqui.</p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Sair</button>
        </form>
    @else
        <p>Você não está logado.</p>
        <p><a href="{{ route('login') }}">Fazer Login</a> ou <a href="{{ route('register') }}">Cadastrar-se</a></p>
    @endauth

    @if (session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div style="color: red;">
            {{ session('error') }}
        </div>
    @endif
</body>

</html>
