<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\TwoFactorVerifyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

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
