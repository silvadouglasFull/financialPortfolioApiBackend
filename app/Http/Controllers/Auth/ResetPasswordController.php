<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    #[OA\Post(
        path: "/password/reset",
        summary: "Reset user password using a token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "some_reset_token", description: "The password reset token received via email."),
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com", description: "The user's email address."),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "new_secure_password", description: "The new password for the user."),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", minLength: 8, example: "new_secure_password", description: "Confirmation of the new password.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 302,
                description: "Redirect to /home on successful password reset."
            ),
            new OA\Response(
                response: 422,
                description: "Validation error (e.g., invalid token, email not found, passwords don't match, or weak password).",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error during password reset."
            )
        ]
    )]
    // The `reset` method is provided by the ResetsPasswords trait.
    // No need to override it here unless custom logic is truly added.
    public function reset(\Illuminate\Http\Request $request)
    {
        return $this->reset($request);
    }
}
