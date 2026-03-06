<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
