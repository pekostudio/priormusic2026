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
        Schema::create('alternate_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alternate_track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->string('mood')->nullable();
            $table->string('music_for')->nullable();
            $table->unsignedInteger('track_number')->nullable();
            $table->string('time')->nullable();
            $table->unsignedInteger('lenght_seconds')->nullable();
            $table->text('comment')->nullable();
            $table->string('composer')->nullable();
            $table->string('publisher')->nullable();
            $table->string('artist')->nullable();
            $table->string('name');
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_id')->constrained()->cascadeOnDelete();
            $table->text('keywords')->nullable();
            $table->longText('lyrics')->nullable();
            $table->string('display_title')->nullable();
            $table->string('genre')->nullable();
            $table->string('tempo')->nullable();
            $table->text('instrumentation')->nullable();
            $table->unsignedInteger('bpm')->nullable();
            $table->unsignedInteger('frequency')->nullable();
            $table->unsignedInteger('bitrate')->nullable();
            $table->timestamp('date_ingested')->nullable();
            $table->string('version')->nullable();
            $table->string('status')->nullable();
            $table->string('cd_code')->nullable();
            $table->boolean('is_alternate')->default(false);
            $table->boolean('is_cached')->default(false);
            $table->unsignedInteger('stem_count')->nullable();
            $table->boolean('library_featured')->default(false);
            $table->boolean('highlighted')->default(false);
            $table->string('originator')->nullable();
            $table->boolean('has_lyrics')->default(false);
            $table->boolean('is_explicit')->default(false);
            $table->date('release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alternate_tracks');
    }
};
