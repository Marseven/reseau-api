<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coffret extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'piece', 'type', 'long', 'lat', 'status', 'zone_id', 'salle_id',
    ];

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
}
