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
        Schema::table('coffrets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
        });

        Schema::table('equipements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
            $table->dropIndex(['vlan']);
        });

        Schema::table('ports', function (Blueprint $table) {
            $table->dropIndex(['vlan']);
            $table->dropIndex(['port_label']);
        });

        Schema::table('metrics', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
        });

        Schema::table('liaisons', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
            $table->dropIndex(['type']);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        }

        // SQLite: query the index list
        $result = DB::select("PRAGMA index_list(`{$table}`)");
        foreach ($result as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }

        return false;
    }
};
