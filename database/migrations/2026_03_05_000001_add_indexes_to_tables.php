<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'coffrets' => ['status', 'name'],
            'equipements' => ['status', 'name', 'vlan'],
            'ports' => ['vlan', 'port_label'],
            'metrics' => ['status', 'name'],
            'liaisons' => ['status'],
            'systems' => ['status', 'name', 'type'],
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $indexName = "{$table}_{$column}_index";
                if (!$this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($column) {
                        $blueprint->index($column);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'coffrets' => ['status', 'name'],
            'equipements' => ['status', 'name', 'vlan'],
            'ports' => ['vlan', 'port_label'],
            'metrics' => ['status', 'name'],
            'liaisons' => ['status'],
            'systems' => ['status', 'name', 'type'],
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                $indexName = "{$table}_{$column}_index";
                if ($this->indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($column) {
                        $blueprint->dropIndex([$column]);
                    });
                }
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        }

        // SQLite
        $result = DB::select("PRAGMA index_list(`{$table}`)");
        foreach ($result as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }

        return false;
    }
};
