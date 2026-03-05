<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coffret extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'piece', 'long', 'lat', 'status'
    ];

    public function equipments()
    {
        return $this->hasMany(Equipement::class);
    }

    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }
}
