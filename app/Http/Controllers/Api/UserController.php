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
use OpenApi\Attributes as OA; // Importe a classe de anotações

#[OA\Tag(
    name: "Users",
    description: "API Endpoints for managing users"
)]
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
    #[OA\Get(
        path: "/api/users",
        summary: "Get a list of all users",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Number of users per page",
                schema: new OA\Schema(type: "integer", default: 10, minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/UserResponse")),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have admin access.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This action is unauthorized."),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve users.")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $users = $this->userService->listUsers((int)$perPage);

            return response()->json($users);
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
    #[OA\Post(
        path: "/api/users",
        summary: "Create a new user",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/UserStoreRequest"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User created successfully!"),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have admin access.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This action is unauthorized."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed.",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to create user.")
                    ]
                )
            )
        ]
    )]
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'message' => 'User created successfully!',
                'user' => $user->only(['id', 'name', 'email', 'document', 'user_type', 'balance', 'created_at'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
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
    #[OA\Get(
        path: "/api/users/{id}",
        summary: "Get a single user by ID",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the user to retrieve",
                schema: new OA\Schema(type: "string", format: "uuid", example: "a1b2c3d4-e5f6-7890-1234-567890abcdef")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/UserResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have access to this user's data.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This action is unauthorized."),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve user.")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
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
    #[OA\Put(
        path: "/api/users/{id}",
        summary: "Update an existing user",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the user to update",
                schema: new OA\Schema(type: "string", format: "uuid", example: "a1b2c3d4-e5f6-7890-1234-567890abcdef")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: "#/components/schemas/UserUpdateRequest"
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "User updated successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User updated successfully!"),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have access to update this user's data.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This action is unauthorized."),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation failed.",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to update user.")
                    ]
                )
            )
        ]
    )]
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
    #[OA\Delete(
        path: "/api/users/{id}",
        summary: "Delete a user",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the user to delete",
                schema: new OA\Schema(type: "string", format: "uuid", example: "a1b2c3d4-e5f6-7890-1234-567890abcdef")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "User deleted successfully."
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - user does not have admin access or cannot delete themselves.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This action is unauthorized."),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User not found.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to delete user: Cannot delete yourself.")
                    ]
                )
            )
        ]
    )]
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return response()->json(['message' => 'User deleted successfully!'], 204);
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
    #[OA\Get(
        path: "/api/profile",
        summary: "Get the authenticated user's profile",
        tags: ["Users"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/UserResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - missing or invalid token.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 500,
                description: "Server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Failed to retrieve user profile.")
                    ]
                )
            )
        ]
    )]
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized or user not found.'], 401);
            }

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
