<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'floor', 'building', 'site_id',
        'status', 'description',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function coffrets()
    {
        return $this->hasMany(Coffret::class);
    }
}
