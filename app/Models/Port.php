<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Port extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'port_label',
        'device_name',
        'poe_enabled',
        'vlan',
        'speed',
        'connected_equipment_id',
        'equipement_id',
        'status',
        'port_type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'poe_enabled' => 'boolean',
        ];
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function connectedEquipment()
    {
        return $this->belongsTo(Equipement::class, 'connected_equipment_id');
    }
}
