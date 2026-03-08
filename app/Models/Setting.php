<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function allAsKeyValue(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    public static function bulkUpdate(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::where('key', $key)->update(['value' => $value]);
        }
    }
}
