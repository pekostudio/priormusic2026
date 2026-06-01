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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('album_track_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('track_number');
            $table->string('name');
            $table->string('display_title')->nullable();
            $table->string('version')->nullable();
            $table->string('time')->nullable();
            $table->unsignedInteger('lenght_seconds')->nullable();
            $table->string('genre')->nullable();
            $table->string('tempo')->nullable();
            $table->unsignedInteger('bpm')->nullable();
            $table->string('composer')->nullable();
            $table->string('publisher')->nullable();
            $table->text('instrumentation')->nullable();
            $table->string('cd_code')->nullable();
            $table->text('comment')->nullable();
            $table->date('release_date')->nullable();
            $table->string('status')->nullable();
            $table->text('keywords')->nullable();
            $table->unsignedInteger('stem_count')->nullable();
            $table->unsignedTinyInteger('is_alternative')->default(0);
            $table->string('api_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
