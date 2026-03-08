<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAudit;
use App\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\TwoFactorLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use OpenApi\Attributes as OA;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/auth/login',
        summary: 'Connexion utilisateur',
        description: 'Authentifie un utilisateur par username/email et mot de passe. Si la 2FA est activée, retourne un token temporaire pour la vérification.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie ou 2FA requise'),
            new OA\Response(response: 401, description: 'Identifiants incorrects'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function login(LoginRequest $request)
    {
        $identifier = $request->email ?? $request->username;
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
            if ($user) {
                LoginAudit::create([
                    'user_id' => $user->id,
                    'action' => 'login_failed',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
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

        LoginAudit::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => 'password',
        ]);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Connexion réussie.');
    }

    #[OA\Post(
        path: '/auth/2fa/challenge',
        summary: 'Vérification 2FA',
        description: 'Vérifie le code OTP ou un code de récupération après un login avec 2FA activée.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['two_factor_token'],
                properties: [
                    new OA\Property(property: 'two_factor_token', type: 'string', description: 'Token temporaire reçu au login'),
                    new OA\Property(property: 'code', type: 'string', example: '123456', description: 'Code OTP 6 chiffres'),
                    new OA\Property(property: 'recovery_code', type: 'string', example: 'ABCD-1234', description: 'Code de récupération (alternatif)'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie'),
            new OA\Response(response: 401, description: 'Token invalide ou expiré'),
            new OA\Response(response: 422, description: 'Code OTP ou récupération invalide'),
        ]
    )]
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

        $method = $request->filled('code') ? '2fa' : 'recovery_code';
        $token = $user->createToken('auth-token')->plainTextToken;

        LoginAudit::create([
            'user_id' => $user->id,
            'action' => '2fa_verified',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $method,
        ]);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Connexion réussie.');
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Déconnexion',
        description: 'Révoque le token courant de l\'utilisateur.',
        tags: ['Authentification'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Déconnexion réussie'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function logout(Request $request)
    {
        LoginAudit::create([
            'user_id' => $request->user()->id,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $token = $request->user()->currentAccessToken();
        if (method_exists($token, 'delete')) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Déconnexion réussie.');
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Utilisateur courant',
        description: 'Retourne les informations de l\'utilisateur authentifié.',
        tags: ['Authentification'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Informations utilisateur'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function me(Request $request)
    {
        return ApiResponse::success($request->user());
    }

    #[OA\Put(
        path: '/auth/profile',
        summary: 'Mise à jour du profil',
        description: 'Met à jour les informations du profil de l\'utilisateur authentifié.',
        tags: ['Authentification'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John'),
                    new OA\Property(property: 'surname', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+241 01 23 45 67'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profil mis à jour'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return ApiResponse::success($user->fresh()->load('site'), 'Profil mis à jour avec succès.');
    }

    #[OA\Put(
        path: '/auth/password',
        summary: 'Changement de mot de passe',
        description: 'Change le mot de passe de l\'utilisateur authentifié.',
        tags: ['Authentification'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe modifié'),
            new OA\Response(response: 422, description: 'Mot de passe actuel incorrect ou validation échouée'),
        ]
    )]
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect.', 422);
        }

        $user->update(['password' => $request->password]);

        return ApiResponse::success(null, 'Mot de passe modifié avec succès.');
    }
}
