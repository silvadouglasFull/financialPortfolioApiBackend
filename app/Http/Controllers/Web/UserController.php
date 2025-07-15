<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User; // Importe o modelo User
use App\Services\User\UserServiceInterface; // Importe a interface do seu UserService
use App\Http\Requests\UserStoreRequest; // Importe o Form Request para criação
use App\Http\Requests\UserUpdateRequest; // Importe o Form Request para atualização
use Illuminate\Http\RedirectResponse; // Para retornos de redirecionamento
use Illuminate\View\View; // Para retornos de view
use Illuminate\Support\Facades\Log; // Para logar erros
use Illuminate\Validation\ValidationException; // Para capturar exceções de validação
use Throwable; // Para capturar exceções genéricas
use Illuminate\Support\Facades\Gate; // Para autorização via Gates/Policies
use Illuminate\Support\Facades\Auth; // Importe o facade Auth

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    /**
     * Construtor do UserController.
     * Injeta o UserService.
     *
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
        // Opcional: Proteger todas as ações do controlador com um middleware de autorização
        // $this->middleware('can:manage-users'); // Exemplo de middleware de Gate/Policy
        // Você pode aplicar middleware aqui se quiser proteger apenas o método 'profile' para usuários logados.
        // $this->middleware('auth')->only('profile');
    }

    /**
     * Display a listing of the users.
     *
     * @return View
     */
    public function index(): View
    {
        // Garante que apenas administradores possam ver a lista de usuários
        Gate::authorize('viewAny', User::class);

        try {
            $users = $this->userService->listUsers(10); // Lista 10 usuários por página

            return view('admin.users.index', compact('users'));
        } catch (Throwable $e) {
            Log::error('Web - Error listing users: ' . $e->getMessage(), ['exception' => $e]);
            return view('admin.error', ['message' => 'Failed to retrieve users.']);
        }
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        // Garante que apenas administradores possam acessar o formulário de criação
        Gate::authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param UserStoreRequest $request
     * @return RedirectResponse
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
        } catch (ValidationException $e) {
            Log::warning('Web - Validation failed during user creation: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return back()->withInput()->withErrors($e->errors());
        } catch (Throwable $e) {
            Log::error('Web - Error storing user: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);
            return back()->withInput()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified user.
     *
     * @param User $user // Injeção de modelo
     * @return View|RedirectResponse
     */
    public function show(User $user): View|RedirectResponse
    {
        // Garante que apenas administradores (ou o próprio usuário, se for o caso) possam ver
        Gate::authorize('view', $user);

        try {
            return view('admin.users.show', compact('user'));
        } catch (Throwable $e) {
            Log::error('Web - Error showing user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $user->id]);
            return back()->withErrors(['error' => 'Failed to retrieve user details.']);
        }
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param User $user // Injeção de modelo
     * @return View|RedirectResponse
     */
    public function edit(User $user): View|RedirectResponse
    {
        // Garante que apenas administradores (ou o próprio usuário, se for o caso) possam editar
        Gate::authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param UserUpdateRequest $request
     * @param User $user // Injeção de modelo
     * @return RedirectResponse
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
        } catch (ValidationException $e) {
            Log::warning('Web - Validation failed during user update: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return back()->withInput()->withErrors($e->errors());
        } catch (Throwable $e) {
            Log::error('Web - Error updating user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $user->id, 'request_data' => $request->all()]);
            return back()->withInput()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param User $user // Injeção de modelo
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        // Garante que apenas administradores podem excluir usuários
        Gate::authorize('delete', $user);

        try {
            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
        } catch (Throwable $e) {
            Log::error('Web - Error deleting user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $user->id]);
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the authenticated user's profile.
     *
     * @return View|RedirectResponse
     */
    public function profile(): View|RedirectResponse
    {
        // Garante que o usuário esteja autenticado para acessar seu próprio perfil.
        // Se a rota já estiver protegida por um middleware 'auth', isso pode ser redundante,
        // mas é uma boa prática para clareza ou se o middleware for mais genérico.
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your profile.');
        }

        try {
            // Obtém o usuário autenticado.
            $user = Auth::user();

            // Você também pode usar o UserService para buscar o usuário por ID
            // para garantir que a lógica de negócio seja sempre através do serviço,
            // mas para o próprio perfil autenticado, Auth::user() é direto.
            // $user = $this->userService->findUserById(Auth::id());

            return view('admin.profile.show', compact('user')); // Retorna a view com os dados do usuário
        } catch (Throwable $e) {
            Log::error('Web - Error retrieving user profile: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return back()->withErrors(['error' => 'Failed to retrieve your profile details.']);
        }
    }
}
