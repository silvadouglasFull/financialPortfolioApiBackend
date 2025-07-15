<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Services\Auth\AuthServiceInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

/**
 * Class GoogleAuthController
 *
 * Controlador responsável por lidar com a autenticação de usuários via Google Socialite.
 */
#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
#[OA\Info(
    version: "1.0.0",
    title: "financialPortfolioApiBackend",
    description: "API Documentation for your application."
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Development Server"
)]

class GoogleAuthController extends Controller
{
    protected AuthServiceInterface $authService;
    protected UserRepositoryInterface $userRepository;

    public function __construct(AuthServiceInterface $authService, UserRepositoryInterface $userRepository)
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    #[OA\Get(
        path: "/google/redirect",
        summary: "Redirect to Google for authentication",
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 302,
                description: "Redirects to Google's authentication page."
            )
        ]
    )]
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    #[OA\Get(
        path: "/google/callback",
        summary: "Handle Google authentication callback",
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful Google login, returns authentication token and user data.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Login com Google realizado com sucesso!"),
                        new OA\Property(property: "token", type: "string", example: "YOUR_SANCTUM_TOKEN"),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserGoogleLoginResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Authentication failed with Google.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Falha na autenticação com Google."),
                        new OA\Property(property: "error", type: "string", example: "Não foi possível autenticar com sua conta Google. Por favor, tente novamente.")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error during user creation.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Ocorreu um erro ao tentar criar o usuário via Google."),
                        new OA\Property(property: "error", type: "string", example: "Por favor, tente novamente.")
                    ]
                )
            )
        ]
    )]
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = $this->userRepository->findByEmail($googleUser->getEmail());

            if (!$user) {
                DB::beginTransaction();
                try {
                    $userData = [
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'document' => null,
                        'password' => Hash::make(Str::random(40)),
                        'user_type' => UserTypeEnum::COMMON->value,
                        'google_id' => $googleUser->getId(),
                    ];

                    $user = $this->userRepository->create($userData);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao criar usuário via Google Socialite: ' . $e->getMessage(), ['google_user_email' => $googleUser->getEmail(), 'exception' => $e]);
                    return response()->json([
                        'message' => 'Ocorreu um erro ao tentar criar o usuário via Google.',
                        'error' => 'Por favor, tente novamente.',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }
            }

            $token = $user->createToken('auth_token_google')->plainTextToken;

            return response()->json([
                'message' => 'Login com Google realizado com sucesso!',
                'token' => $token,
                'user' => $user->makeHidden(['password']),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Erro no callback do Google Socialite: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Falha na autenticação com Google.',
                'error' => 'Não foi possível autenticar com sua conta Google. Por favor, tente novamente.',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}

#[OA\Schema(
    schema: "UserGoogleLoginResponse",
    title: "User Google Login Response",
    description: "User data returned after successful Google login.",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "a1b2c3d4-e5f6-7890-1234-567890abcdef"),
        new OA\Property(property: "name", type: "string", example: "John Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "document", type: "string", nullable: true, example: null),
        new OA\Property(property: "balance", type: "number", format: "float", example: 0.00),
        new OA\Property(property: "user_type", ref: "#/components/schemas/UserTypeEnum"),
        new OA\Property(property: "google_id", type: "string", nullable: true, example: "102345678901234567890"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2023-10-27T10:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2023-10-27T10:00:00.000000Z"),
        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true, example: null)
    ],
    type: "object"
)]
class UserGoogleLoginResponse {} // Classe fictícia para o schema de resposta