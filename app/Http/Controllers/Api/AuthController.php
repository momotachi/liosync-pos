<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Optional: Check if user is active/verified if needed

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUser($request->user()),
        ]);
    }

     public function refresh(Request $request)
    {
        // Sanctum tokens are long-lived, but we can issue a new one if needed
        $request->user()->currentAccessToken()->delete();
        $token = $request->user()->createToken('Refreshed Token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token
            ]
        ]);
    }

    private function formatUser($user)
    {
        $role = $user->role; // Default to existing column fallback

        // Try to get from Spatie if available
        try {
            $spatieRoles = $user->getRoleNames();
            if ($spatieRoles && $spatieRoles->isNotEmpty()) {
                $role = $spatieRoles->first();
            }
        } catch (\Throwable $e) {
            // Keep default role if Spatie fails
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
            ] : null,
            'branch' => $user->branch ? [
                'id' => $user->branch->id,
                'name' => $user->branch->name,
            ] : null,
            // 'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
