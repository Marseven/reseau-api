<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vlan_id', 'name', 'description', 'site_id',
        'network', 'gateway', 'status',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
