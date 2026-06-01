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
        Schema::create('music_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('album_track_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->dateTime('occurred_at')->index();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('track_title')->nullable();
            $table->string('album_title')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index(['album_track_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
            $table->index(['album_track_id', 'event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_usage_events');
    }
};
