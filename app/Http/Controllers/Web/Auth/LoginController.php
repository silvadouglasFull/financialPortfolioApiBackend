<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Importar o LoginRequest
use App\Services\Auth\AuthServiceInterface; // Importar a interface do serviço de autenticação
use App\Exceptions\AuthenticationException; // Importar a exceção de autenticação
use Illuminate\Http\RedirectResponse; // Para tipagem do retorno
use Illuminate\Support\Facades\Auth; // Para fazer o login na sessão
use Illuminate\Support\Facades\Log; // Para logs

class LoginController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    /**
     * Lida com a requisição de login de usuário via web.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            // Tenta autenticar o usuário usando o AuthService
            // Se bem-sucedido, Auth::attemptLogin provavelmente já loga o usuário na sessão ou
            // retorna os dados que você usará para logar.
            // Vou assumir que Auth::login($user) será necessário após o sucesso do serviço.
            $result = $this->authService->attemptLogin($request->only('email', 'password'));
            // Se o attemptLogin for bem-sucedido, significa que as credenciais são válidas.
            // Agora, precisamos garantir que o usuário esteja logado na sessão web.
            // O método `attemptLogin` do seu serviço deve retornar uma instância de usuário
            // ou um booleano de sucesso. Se retornar o user, podemos logá-lo:
            if ($result && isset($result['user'])) {
                Auth::guard('web')->login($result['user']); // Faz o login do usuário na sessão Laravel
                $request->session()->regenerate(); // Para segurança, regenera a sessão

                // Redireciona o usuário para a página '/'
                // `intended()` tenta redirecionar para a URL que o usuário estava tentando acessar
                // antes de ser interceptado pelo middleware de autenticação.
                // Se não houver uma URL "intencionada" (ex: ele acessou o login diretamente),
                // ele redireciona para o fallback (neste caso, '/').
                return redirect()->intended('/');
            }

            // Se o serviço não retornar um user mas não lançar exceção, indica falha implícita
            return back()->withErrors(['login' => 'Credenciais inválidas.'])->withInput();
        } catch (AuthenticationException $e) {
            // Captura a exceção de autenticação personalizada para credenciais inválidas
            return back()->withErrors(['login' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            // Captura quaisquer outras exceções inesperadas
            Log::error('Erro ao tentar fazer login via web: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['login' => 'Ocorreu um erro interno ao tentar realizar o login. Por favor, tente novamente mais tarde.'])->withInput();
        }
    }
}
