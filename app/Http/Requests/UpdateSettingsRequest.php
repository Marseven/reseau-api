<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator');
    }

    public function rules(): array
    {
        return [
            // General
            'company_name' => 'sometimes|string|max:255',
            'timezone' => 'sometimes|string|in:europe-paris,europe-london,america-newyork',
            'language' => 'sometimes|string|in:fr,en,es',
            'refresh_interval' => 'sometimes|integer|min:5|max:300',

            // Network
            'snmp_community' => 'sometimes|string|max:255',
            'snmp_timeout' => 'sometimes|integer|min:1000|max:30000',
            'snmp_retries' => 'sometimes|integer|min:1|max:10',
            'auto_discovery' => 'sometimes|string|in:true,false',

            // Notifications
            'notify_equipment_alerts' => 'sometimes|string|in:true,false',
            'notify_maintenance' => 'sometimes|string|in:true,false',
            'notify_performance' => 'sometimes|string|in:true,false',
            'notification_email' => 'sometimes|nullable|email|max:255',
            'notification_webhook' => 'sometimes|nullable|url|max:500',

            // Security
            'session_timeout' => 'sometimes|integer|min:5|max:480',
            'password_policy' => 'sometimes|string|in:low,medium,high',

            // Logging
            'audit_logs_enabled' => 'sometimes|string|in:true,false',
            'log_retention_days' => 'sometimes|integer|min:7|max:365',
            'log_level' => 'sometimes|string|in:error,warn,info,debug',

            // Database
            'backup_frequency' => 'sometimes|string|in:hourly,daily,weekly',
            'data_retention_months' => 'sometimes|integer|min:1|max:120',
            'compress_old_data' => 'sometimes|string|in:true,false',
        ];
    }
}
