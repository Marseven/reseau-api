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
    ];

    public function fromEquipement()
    {
        return $this->belongsTo(Equipement::class, 'from');
    }

    public function toEquipement()
    {
        return $this->belongsTo(Equipement::class, 'to');
    }
}
