<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coffrets', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('status')->constrained('zones')->nullOnDelete();
            $table->string('type')->nullable()->after('piece');
        });
    }

    public function down(): void
    {
        Schema::table('coffrets', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['zone_id', 'type']);
        });
    }
};
