<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batiment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'zone_id', 'address', 'floors_count',
        'longitude', 'latitude', 'status', 'description',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function salles()
    {
        return $this->hasMany(Salle::class);
    }
}
