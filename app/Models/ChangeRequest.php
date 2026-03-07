<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ChangeRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'code',
        'coffret_id',
        'requester_id',
        'type',
        'description',
        'justification',
        'photo_before',
        'photo_after',
        'intervention_date',
        'status',
        'reviewer_id',
        'reviewed_at',
        'review_comment',
        'snapshot_before',
        'snapshot_after',
    ];

    protected $casts = [
        'intervention_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'snapshot_before' => 'array',
        'snapshot_after' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ChangeRequest $changeRequest) {
            if (empty($changeRequest->code)) {
                $changeRequest->code = 'CR-' . strtoupper(Str::random(5));
            }
        });
    }

    public function coffret()
    {
        return $this->belongsTo(Coffret::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
