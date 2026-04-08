<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    // -------------------------------------------------------------------------
    // Write — called from other services
    // -------------------------------------------------------------------------

    /**
     * Notify a single user.
     */
    public function notify(
        User   $recipient,
        string $type,
        string $title,
        string $body  = '',
        array  $data  = [],
    ): Notification {
        return Notification::create([
            'user_id' => $recipient->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);
        
        // BelongsToTenant auto-attaches tenant_id via the creating hook
    }

    /**
     * Notify all users in the current tenant except the actor.
     * Used for "teammate did X" events.
     */
    public function notifyTeam(
        string $type,
        string $title,
        string $body = '',
        array  $data = [],
    ): void {
        $actorId  = Auth::id();
        $tenantId = Auth::user()->tenant_id;

        $recipients = User::where('tenant_id', $tenantId)
            ->where('id', '!=', $actorId)
            ->pluck('id');

        if ($recipients->isEmpty()) return;

        $now  = now();
        $rows = $recipients->map(fn ($uid) => [
            'id'         => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id'  => $tenantId,
            'user_id'    => $uid,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'data'       => json_encode($data),
            'read_at'    => null,
            'created_at' => $now,
        ])->all();

        DB::table('notifications')->insert($rows);
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function unreadCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    // -------------------------------------------------------------------------
    // Mutations
    // -------------------------------------------------------------------------

    public function markRead(string $id): void
    {
        Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}