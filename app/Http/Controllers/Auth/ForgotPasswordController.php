<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authentication",
    description: "API Endpoints para autenticação de usuários"
)]
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    #[OA\Post(
        path: "/password/email",
        summary: "Send password reset link to user's email",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com", description: "The user's email address.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password reset link sent successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "We have emailed your password reset link!"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error (e.g., email not found, invalid email format).",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Could not send password reset link."),
                    ]
                )
            )
        ]
    )]
    public function sendResetLinkEmail()
    {
        // This method is provided by the SendsPasswordResetEmails trait.
        // The implementation usually involves validating the email and then sending the reset link.
        // The trait handles the actual response, but OpenAPI needs the definition.
        return $this->sendResetLinkEmail(request());
    }
}
