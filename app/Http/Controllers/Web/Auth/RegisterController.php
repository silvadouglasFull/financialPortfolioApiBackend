<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest; // Reutiliza o Request
use App\Services\User\UserServiceInterface; // Reutiliza o Service
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisterController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Exibe o formulário de registro.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Lida com a requisição de registro de um novo usuário via web.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            dd($validatedData);
            $user = $this->userService->createUser($validatedData);

            Auth::login($user); // Loga o usuário após o registro

            return redirect()->route('dashboard')->with('success', 'Cadastro realizado com sucesso!');
        } catch (Throwable $e) {
            Log::error("Erro no registro de usuário via web: " . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);

            return back()->withInput()->withErrors(['registration' => 'Ocorreu um erro ao registrar: ' . $e->getMessage()]);
        }
    }
}
