<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->string('classification')->default('IT')->after('type');
            $table->string('serial_number')->nullable()->after('classification');
            $table->string('fabricant')->nullable()->after('serial_number');
            $table->string('modele')->nullable()->after('fabricant');
            $table->string('connection_type')->nullable()->after('modele');
        });
    }

    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropColumn(['classification', 'serial_number', 'fabricant', 'modele', 'connection_type']);
        });
    }
};
