<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liaison extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from',
        'to',
        'label',
        'media',
        'length',
        'status',
        'from_port_id',
        'to_port_id',
        'status_label',
    ];

    public function fromEquipement()
    {
        return $this->belongsTo(Equipement::class, 'from');
    }

    public function toEquipement()
    {
        return $this->belongsTo(Equipement::class, 'to');
    }

    public function fromPort()
    {
        return $this->belongsTo(Port::class, 'from_port_id');
    }

    public function toPort()
    {
        return $this->belongsTo(Port::class, 'to_port_id');
    }
}
