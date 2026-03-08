<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\TwoFactorVerifyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use OpenApi\Attributes as OA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    #[OA\Post(
        path: '/auth/2fa/setup',
        summary: 'Configurer la 2FA',
        security: [['sanctum' => []]],
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: 'Secret et URI généré'),
            new OA\Response(response: 400, description: '2FA déjà activée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return ApiResponse::error('La 2FA est déjà activée.', 400);
        }

        $secret = $this->google2fa->generateSecretKey();

        $user->two_factor_secret = $secret;
        $user->save();

        $provisioningUri = $this->google2fa->getQRCodeUrl(
            config('app.name', 'ReseauApp'),
            $user->email,
            $secret
        );

        return ApiResponse::success([
            'secret' => $secret,
            'provisioning_uri' => $provisioningUri,
        ], 'Secret 2FA généré.');
    }

    #[OA\Post(
        path: '/auth/2fa/verify',
        summary: 'Activer la 2FA',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', description: 'Code OTP'),
                ]
            )
        ),
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: '2FA activée + codes de récupération'),
            new OA\Response(response: 400, description: 'Erreur'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Code invalide'),
        ]
    )]
    public function verify(TwoFactorVerifyRequest $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return ApiResponse::error('La 2FA est déjà activée.', 400);
        }

        if (!$user->two_factor_secret) {
            return ApiResponse::error('Veuillez d\'abord configurer la 2FA via /2fa/setup.', 400);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return ApiResponse::error('Code OTP invalide.', 422);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->two_factor_enabled = true;
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        return ApiResponse::success([
            'recovery_codes' => $recoveryCodes,
        ], '2FA activée avec succès.');
    }

    #[OA\Post(
        path: '/auth/2fa/disable',
        summary: 'Désactiver la 2FA',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', description: 'Code OTP'),
                ]
            )
        ),
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: '2FA désactivée'),
            new OA\Response(response: 400, description: 'Erreur'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Code invalide'),
        ]
    )]
    public function disable(TwoFactorVerifyRequest $request)
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return ApiResponse::error('La 2FA n\'est pas activée.', 400);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return ApiResponse::error('Code OTP invalide.', 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return ApiResponse::success(null, '2FA désactivée avec succès.');
    }

    #[OA\Get(
        path: '/auth/2fa/recovery-codes',
        summary: 'Nombre de codes de récupération',
        security: [['sanctum' => []]],
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: 'Nombre de codes restants'),
            new OA\Response(response: 400, description: '2FA non activée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
        ]
    )]
    public function recoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return ApiResponse::error('La 2FA n\'est pas activée.', 400);
        }

        $codes = $user->getDecryptedRecoveryCodes();

        return ApiResponse::success([
            'count' => count($codes),
        ]);
    }

    #[OA\Post(
        path: '/auth/2fa/recovery-codes/regenerate',
        summary: 'Régénérer les codes de récupération',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', description: 'Code OTP'),
                ]
            )
        ),
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: 'Codes de récupération régénérés'),
            new OA\Response(response: 400, description: '2FA non activée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Code invalide'),
        ]
    )]
    public function regenerateRecoveryCodes(TwoFactorVerifyRequest $request)
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return ApiResponse::error('La 2FA n\'est pas activée.', 400);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return ApiResponse::error('Code OTP invalide.', 422);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        return ApiResponse::success([
            'recovery_codes' => $recoveryCodes,
        ], 'Codes de récupération régénérés.');
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        }
        return $codes;
    }
}
