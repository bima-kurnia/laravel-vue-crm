<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Tenant-scoped user list for owner autocomplete in DealForm.
     */
    public function index(ListUserRequest $request): AnonymousResourceCollection
    {
        $query = User::where('tenant_id', auth()->user()->tenant_id);

        if ($search = $request->validated('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
           ->paginate($request->validated('per_page', 15));

        return UserResource::collection($users);
    }
}
