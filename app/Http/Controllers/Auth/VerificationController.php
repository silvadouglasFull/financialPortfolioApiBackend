<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    #[OA\Get(
        path: "/email/verify/{id}/{hash}",
        summary: "Verify user's email address",
        description: "Verifies the user's email address using the provided ID and hash from the verification link. This is typically a web route.",
        tags: ["Authentication"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The user's ID.",
                schema: new OA\Schema(type: "string", format: "uuid")
            ),
            new OA\Parameter(
                name: "hash",
                in: "path",
                required: true,
                description: "The verification hash.",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 302,
                description: "Redirect to /home on successful email verification.",
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - if the user is not authenticated.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - if the link is invalid or expired (e.g., 'signed' middleware failure).",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "This email verification link is invalid or has expired."),
                    ]
                )
            )
        ]
    )]
    // The `verify` method is provided by the VerifiesEmails trait.
    // No need to override it here unless custom logic is truly added.
    public function verify(\Illuminate\Http\Request $request)
    {
        return $this->verify($request);
    }


    #[OA\Post(
        path: "/email/resend",
        summary: "Resend email verification link",
        description: "Sends a new email verification link to the authenticated user's email address.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Verification email resent successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "A fresh verification link has been sent to your email address."),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized - if the user is not authenticated.",
                content: new OA\JsonContent(ref: "#/components/schemas/AuthenticationError")
            ),
            new OA\Response(
                response: 429,
                description: "Too Many Requests - if the user tries to resend too frequently (throttled).",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Too Many Attempts."),
                    ]
                )
            )
        ]
    )]
    // The `resend` method is provided by the VerifiesEmails trait.
    // No need to override it here unless custom logic is truly added.
    public function resend(\Illuminate\Http\Request $request)
    {
        return $this->resend($request);
    }
}
