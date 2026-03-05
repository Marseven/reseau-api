<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coffrets', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
        });

        Schema::table('equipements', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
            $table->index('vlan');
        });

        Schema::table('ports', function (Blueprint $table) {
            $table->index('vlan');
            $table->index('port_label');
        });

        Schema::table('metrics', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
        });

        Schema::table('liaisons', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
            $table->index('type');
        });
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
};
