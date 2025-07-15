<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View; // Importar a classe View

/**
 * Class LoginController
 *
 * Controlador responsável por exibir a página de login para usuários via web.
 */
class LoginController extends Controller
{
    /**
     * Exibe o formulário de login.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('auth.login'); // Retorna a view 'auth/login.blade.php'
    }
}
