<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Support\PortalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::with(['firm', 'client'])
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->firm) {
            throw ValidationException::withMessages([
                'email' => ['This user is not assigned to a firm workspace.'],
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken('portal')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => PortalData::user($user),
            'firm' => PortalData::firm($user->firm),
            'client' => $user->client ? PortalData::client($user->client) : null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['firm', 'client']);

        return response()->json([
            'user' => PortalData::user($user),
            'firm' => PortalData::firm($user->firm),
            'client' => $user->client ? PortalData::client($user->client) : null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
