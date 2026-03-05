<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $identifier = $request->username;
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        $user = User::where(function ($query) use ($identifier, $isEmail) {
                        if ($isEmail) {
                            $query->where('email', $identifier);
                        } else {
                            $query->where('username', $identifier);
                        }
                    })
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::unauthorized('Les informations d\'identification fournies sont incorrectes.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Connexion réussie.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Déconnexion réussie.');
    }

    public function me(Request $request)
    {
        return ApiResponse::success($request->user());
    }
}
