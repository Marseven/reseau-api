<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('batiment_id')->constrained()->onDelete('cascade');
            $table->string('floor')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('batiment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salles');
    }
};
