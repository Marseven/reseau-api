<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'code', 'title', 'description', 'type', 'priority', 'status',
        'equipement_id', 'coffret_id', 'site_id',
        'technicien_id', 'validator_id',
        'scheduled_date', 'scheduled_time',
        'started_at', 'completed_at', 'duration_minutes', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function coffret()
    {
        return $this->belongsTo(Coffret::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
