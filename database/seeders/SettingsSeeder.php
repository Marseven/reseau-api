<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'company_name', 'value' => 'TelecomNet SA', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'europe-paris', 'group' => 'general'],
            ['key' => 'language', 'value' => 'fr', 'group' => 'general'],
            ['key' => 'refresh_interval', 'value' => '30', 'group' => 'general'],

            // Network
            ['key' => 'snmp_community', 'value' => 'public', 'group' => 'network'],
            ['key' => 'snmp_timeout', 'value' => '5000', 'group' => 'network'],
            ['key' => 'snmp_retries', 'value' => '3', 'group' => 'network'],
            ['key' => 'auto_discovery', 'value' => 'true', 'group' => 'network'],

            // Notifications
            ['key' => 'notify_equipment_alerts', 'value' => 'true', 'group' => 'notifications'],
            ['key' => 'notify_maintenance', 'value' => 'true', 'group' => 'notifications'],
            ['key' => 'notify_performance', 'value' => 'false', 'group' => 'notifications'],
            ['key' => 'notification_email', 'value' => '', 'group' => 'notifications'],
            ['key' => 'notification_webhook', 'value' => '', 'group' => 'notifications'],

            // Security
            ['key' => 'session_timeout', 'value' => '60', 'group' => 'security'],
            ['key' => 'password_policy', 'value' => 'medium', 'group' => 'security'],

            // Logging
            ['key' => 'audit_logs_enabled', 'value' => 'true', 'group' => 'logging'],
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'logging'],
            ['key' => 'log_level', 'value' => 'info', 'group' => 'logging'],

            // Database
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'database'],
            ['key' => 'data_retention_months', 'value' => '12', 'group' => 'database'],
            ['key' => 'compress_old_data', 'value' => 'true', 'group' => 'database'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
