<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->boolean('featured')->default(false);
            $table->longText('detail')->nullable();
            $table->string('name');
            $table->string('library_id')->unique();
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->string('library_logo_url')->nullable();
            $table->boolean('status')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->json('codes')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
