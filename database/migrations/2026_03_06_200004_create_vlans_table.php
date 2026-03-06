<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vlans', function (Blueprint $table) {
            $table->id();
            $table->integer('vlan_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('network')->nullable();
            $table->string('gateway')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vlans');
    }
};
