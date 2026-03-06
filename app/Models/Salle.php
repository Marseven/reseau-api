<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'batiment_id', 'floor', 'type',
        'status', 'description',
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function coffrets()
    {
        return $this->hasMany(Coffret::class);
    }
}
