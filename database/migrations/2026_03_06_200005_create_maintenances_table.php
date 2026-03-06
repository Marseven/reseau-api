<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('priority');
            $table->string('status')->default('planifiee');
            $table->foreignId('equipement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coffret_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technicien_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index('priority');
            $table->index('technicien_id');
            $table->index('scheduled_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
