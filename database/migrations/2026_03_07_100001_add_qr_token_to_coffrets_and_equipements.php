<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coffrets', function (Blueprint $table) {
            $table->uuid('qr_token')->unique()->nullable()->after('status');
        });

        Schema::table('equipements', function (Blueprint $table) {
            $table->uuid('qr_token')->unique()->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('coffrets', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });

        Schema::table('equipements', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
