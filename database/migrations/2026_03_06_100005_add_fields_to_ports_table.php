<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->foreignId('equipement_id')->nullable()->after('id')->constrained('equipements')->cascadeOnDelete();
            $table->string('status')->default('active')->after('connected_equipment_id');
            $table->string('port_type')->nullable()->after('status');
            $table->text('description')->nullable()->after('port_type');
        });
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropForeign(['equipement_id']);
            $table->dropColumn(['equipement_id', 'status', 'port_type', 'description']);
        });
    }
};
