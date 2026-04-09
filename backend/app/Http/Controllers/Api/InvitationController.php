<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Http\Requests\Invitation\SendInvitationRequest;
use App\Http\Resources\UserResource;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    /**
     * POST /api/invitations
     * Owner/admin sends an invite — protected, role-gated.
     */
    public function send(SendInvitationRequest $request): JsonResponse
    {
        $invitation = $this->invitationService->send(
            email: $request->validated('email'),
            role:  $request->validated('role', 'member'),
        );

        return response()->json([
            'message'    => 'Invitation sent to ' . $invitation->email,
            'invitation' => [
                'id'         => $invitation->id,
                'email'      => $invitation->email,
                'role'       => $invitation->role,
                'expires_at' => $invitation->expires_at,
            ],
        ], 201);
    }

    /**
     * GET /api/invitations/validate/{token}
     * Public — recipient checks the token is still valid before showing the form.
     */
    public function validate(string $token): JsonResponse
    {
        try {
            $invitation = $this->invitationService->findValid($token);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'email'       => $invitation->email,
            'role'        => $invitation->role,
            'tenant_name' => $invitation->tenant->name,
            'invited_by'  => $invitation->invitedBy->name,
            'expires_at'  => $invitation->expires_at,
        ]);
    }

    /**
     * POST /api/invitations/accept/{token}
     * Public — recipient submits their name + password.
     */
    public function accept(AcceptInvitationRequest $request, string $token): JsonResponse
    {
        try {
            $result = $this->invitationService->accept(
                token:    $token,
                name:     $request->validated('name'),
                password: $request->validated('password'),
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 201);
    }

    /**
     * GET /api/invitations
     * List pending invitations — protected, role-gated.
     */
    public function index(): JsonResponse
    {
        $invitations = $this->invitationService->listPending();

        return response()->json([
            'data' => $invitations->map(fn ($i) => [
                'id'         => $i->id,
                'email'      => $i->email,
                'role'       => $i->role,
                'invited_by' => $i->invitedBy->name,
                'expires_at' => $i->expires_at,
            ]),
        ]);
    }

    /**
     * DELETE /api/invitations/{id}
     * Revoke a pending invitation — protected, role-gated.
     */
    public function revoke(string $id): JsonResponse
    {
        $this->invitationService->revoke($id);

        return response()->json(['message' => 'Invitation revoked.']);
    }
}
