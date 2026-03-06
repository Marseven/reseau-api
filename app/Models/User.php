<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'surname',
        'username',
        'phone',
        'role',
        'email',
        'is_active',
        'password',
        'site_id',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_secret;
    }

    public function getDecryptedRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode($this->two_factor_recovery_codes, true) ?? [];
    }

    public function replaceRecoveryCode(string $usedCode): void
    {
        $codes = $this->getDecryptedRecoveryCodes();
        $codes = array_values(array_filter($codes, fn($code) => $code !== $usedCode));

        $this->two_factor_recovery_codes = json_encode($codes);
        $this->save();
    }
}
