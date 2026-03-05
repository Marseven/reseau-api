<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class System extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'description', 'vendor', 'endpoint', 'monitored_scope', 'status'
    ];
}
