<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    // -------------------------------------------------------------------------
    // Send
    // -------------------------------------------------------------------------

    public function send(string $email, string $role = 'member'): Invitation
    {
        $tenantId = Auth::user()->tenant_id;

        // Prevent inviting someone already in this tenant
        $alreadyMember = User::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'email' => 'This email address already belongs to a member of your team.',
            ]);
        }

        // Revoke any existing pending invitation for this email in this tenant
        Invitation::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = Invitation::create([
            'invited_by' => Auth::id(),
            'email'      => $email,
            'role'       => $role,
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load(['invitedBy', 'tenant']);

        Mail::to($email)->send(new InvitationMail($invitation));

        return $invitation;
    }

    // -------------------------------------------------------------------------
    // Validate token (called by the frontend before showing the form)
    // -------------------------------------------------------------------------

    public function findValid(string $token): Invitation
    {
        $invitation = Invitation::withoutGlobalScopes()
            ->with(['tenant', 'invitedBy'])
            ->where('token', $token)
            ->first();

        if (! $invitation) {
            throw ValidationException::withMessages([
                'token' => 'This invitation link is invalid.',
            ]);
        }

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'token' => $invitation->accepted_at
                    ? 'This invitation has already been accepted.'
                    : 'This invitation has expired. Please ask for a new one.',
            ]);
        }

        return $invitation;
    }

    // -------------------------------------------------------------------------
    // Accept
    // -------------------------------------------------------------------------

    public function accept(string $token, string $name, string $password): array
    {
        $invitation = $this->findValid($token);

        return DB::transaction(function () use ($invitation, $name, $password) {
            $user = User::create([
                'tenant_id' => $invitation->tenant_id,
                'name'      => $name,
                'email'     => $invitation->email,
                'password'  => Hash::make($password),
                'role'      => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);

            $sanctumToken = $user->createToken('web')->plainTextToken;

            return [
                'token' => $sanctumToken,
                'user'  => $user,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // List pending invitations (for the team management UI)
    // -------------------------------------------------------------------------

    public function listPending(): \Illuminate\Database\Eloquent\Collection
    {
        return Invitation::with('invitedBy')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Revoke
    // -------------------------------------------------------------------------

    public function revoke(string $id): void
    {
        $invitation = Invitation::findOrFail($id);
        $invitation->delete();
    }
}