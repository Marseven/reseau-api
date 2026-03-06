<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'equipement_code',
        'name',
        'type',
        'classification',
        'serial_number',
        'fabricant',
        'modele',
        'connection_type',
        'description',
        'direction_in_out',
        'vlan',
        'ip_address',
        'coffret_id',
        'status',
    ];

    public function coffret()
    {
        return $this->belongsTo(Coffret::class);
    }

    public function ports()
    {
        return $this->hasMany(Port::class, 'equipement_id');
    }

    public function connectedPorts()
    {
        return $this->hasMany(Port::class, 'connected_equipment_id');
    }
}
