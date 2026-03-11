<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Coffret extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'code', 'name', 'piece', 'type', 'long', 'lat', 'status', 'photo', 'zone_id', 'salle_id', 'qr_token',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Coffret $coffret) {
            if (empty($coffret->qr_token)) {
                $coffret->qr_token = Str::uuid()->toString();
            }
        });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipement::class);
    }

    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }
}
