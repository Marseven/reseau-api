<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            static::logActivity($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            unset($dirty['updated_at']);

            if (empty($dirty)) {
                return;
            }

            $oldValues = [];
            foreach (array_keys($dirty) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            static::logActivity($model, 'updated', $oldValues, $dirty);
        });

        static::deleted(function ($model) {
            static::logActivity($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function logActivity($model, string $action, ?array $oldValues, ?array $newValues): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
