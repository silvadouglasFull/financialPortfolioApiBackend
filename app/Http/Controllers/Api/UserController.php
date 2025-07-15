<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // Certifique-se de importar o modelo User
use App\Services\User\UserServiceInterface; // Importe a interface do seu UserService
use App\Http\Requests\UserStoreRequest; // Importe o Form Request para criação
use App\Http\Requests\UserUpdateRequest; // Importe o Form Request para atualização
use Illuminate\Http\JsonResponse; // Para retornos JSON
use Illuminate\Http\Request; // Para o método index, se não usar Request específico
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;
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
        // Opcional: Aplicar middleware para proteção de rotas API
        // Certifique-se de que a rota 'profile' esteja protegida por autenticação.
        // Se a rota estiver em um grupo de rotas com 'auth:sanctum', não precisa disso.
        // $this->middleware('auth:sanctum', ['only' => ['profile']]); // Exemplo para proteger apenas o método profile
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10); // Permite definir itens por página
            $users = $this->userService->listUsers((int)$perPage);

            return response()->json($users); // Retorna os usuários paginados em JSON
        } catch (Throwable $e) {
            Log::error('API - Error listing users: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to retrieve users.'], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param UserStoreRequest $request
     * @return JsonResponse
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'message' => 'User created successfully!',
                'user' => $user->only(['id', 'name', 'email', 'document', 'user_type', 'balance', 'created_at'])
            ], 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (Throwable $e) {
            Log::error('API - Error storing user: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);
            return response()->json(['message' => 'Failed to create user.'], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404); // 404 Not Found
            }

            return response()->json($user);
        } catch (Throwable $e) {
            Log::error('API - Error showing user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $id]);
            return response()->json(['message' => 'Failed to retrieve user.'], 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param UserUpdateRequest $request
     * @param User $user // Injeção de modelo para o usuário a ser atualizado
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'message' => 'User updated successfully!',
                'user' => $updatedUser->only(['id', 'name', 'email', 'document', 'user_type', 'balance', 'updated_at'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            Log::error('API - Error updating user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $user->id, 'request_data' => $request->all()]);
            return response()->json(['message' => 'Failed to update user.'], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param User $user // Injeção de modelo para o usuário a ser excluído
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return response()->json(['message' => 'User deleted successfully!'], 204); // 204 No Content
        } catch (Throwable $e) {
            Log::error('API - Error deleting user: ' . $e->getMessage(), ['exception' => $e, 'user_id' => $user->id]);
            return response()->json(['message' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user(); // Obtém o usuário autenticado via Sanctum ou outra guarda

            if (!$user) {
                // Embora 'auth:sanctum' já impediria isso, é uma boa prática defensiva.
                return response()->json(['message' => 'Unauthorized or user not found.'], 401); // 401 Unauthorized
            }

            // Retorna apenas os campos desejados para o perfil
            return response()->json($user->only([
                'id',
                'name',
                'email',
                'document',
                'user_type',
                'balance',
                'created_at',
                'updated_at',
                'email_verified_at',
            ]));
        } catch (Throwable $e) {
            Log::error('API - Error retrieving user profile: ' . $e->getMessage(), ['exception' => $e, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Failed to retrieve user profile.'], 500);
        }
    }
}
