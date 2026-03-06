<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liaisons', function (Blueprint $table) {
            $table->foreignId('from_port_id')->nullable()->after('to')->constrained('ports')->nullOnDelete();
            $table->foreignId('to_port_id')->nullable()->after('from_port_id')->constrained('ports')->nullOnDelete();
            $table->string('status_label')->default('active')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('liaisons', function (Blueprint $table) {
            $table->dropForeign(['from_port_id']);
            $table->dropForeign(['to_port_id']);
            $table->dropColumn(['from_port_id', 'to_port_id', 'status_label']);
        });
    }
};
