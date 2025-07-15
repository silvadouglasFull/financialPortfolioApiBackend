<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverter Transação (Admin)</title>
</head>

<body>
    <h1>Reverter Transação</h1>

    @auth
        @if (Auth::user()->user_type->value === 'ADMIN')
            <p>Olá, Administrador {{ Auth::user()->name }}!</p>

            <form method="POST" action="{{ route('admin.reversals.store') }}">
                @csrf
                <div>
                    <label for="original_transaction_id">ID da Transação Original:</label>
                    <input type="text" id="original_transaction_id" name="original_transaction_id"
                        value="{{ old('original_transaction_id') }}" required>
                </div>
                <div>
                    <label for="reason">Motivo da Reversão:</label>
                    <textarea id="reason" name="reason" rows="4" required>{{ old('reason') }}</textarea>
                </div>
                <div>
                    <button type="submit">Reverter Transação</button>
                </div>
                @if ($errors->any())
                    <div style="color: red;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div style="color: green;">
                        {{ session('success') }}
                    </div>
                @endif
            </form>
            <p><a href="{{ route('transactions.index') }}">Voltar ao Dashboard</a></p>
        @else
            <p style="color: red;">Você não tem permissão para acessar esta página.</p>
        @endif
    @else
        <p>Você precisa estar logado para acessar esta página. <a href="{{ route('login') }}">Do Login</a></p>
    @endauth
</body>

</html>
