<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('coffret_id')->constrained()->onDelete('cascade');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->text('description');
            $table->text('justification');
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->dateTime('intervention_date');
            $table->string('status')->default('en_attente');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->json('snapshot_before');
            $table->json('snapshot_after')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'coffret_id', 'requester_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
