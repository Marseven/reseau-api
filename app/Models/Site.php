<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'address', 'city', 'country',
        'longitude', 'latitude', 'status', 'description',
    ];

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
