<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\TwoFactorLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

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

        if ($user->hasTwoFactorEnabled()) {
            $twoFactorToken = Crypt::encryptString(json_encode([
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(5)->toIso8601String(),
            ]));

            return ApiResponse::success([
                'requires_2fa' => true,
                'two_factor_token' => $twoFactorToken,
            ], 'Vérification 2FA requise.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Connexion réussie.');
    }

    public function verifyTwoFactorLogin(TwoFactorLoginRequest $request)
    {
        try {
            $payload = json_decode(Crypt::decryptString($request->two_factor_token), true);
        } catch (\Exception $e) {
            return ApiResponse::unauthorized('Token 2FA invalide ou expiré.');
        }

        if (now()->isAfter($payload['expires_at'])) {
            return ApiResponse::unauthorized('Token 2FA expiré. Veuillez vous reconnecter.');
        }

        $user = User::find($payload['user_id']);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return ApiResponse::unauthorized('Utilisateur introuvable ou 2FA non activée.');
        }

        if ($request->filled('code')) {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (!$valid) {
                return ApiResponse::error('Code OTP invalide.', 422);
            }
        } elseif ($request->filled('recovery_code')) {
            $recoveryCodes = $user->getDecryptedRecoveryCodes();

            if (!in_array($request->recovery_code, $recoveryCodes)) {
                return ApiResponse::error('Code de récupération invalide.', 422);
            }

            $user->replaceRecoveryCode($request->recovery_code);
        } else {
            return ApiResponse::error('Un code OTP ou un code de récupération est requis.', 422);
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
