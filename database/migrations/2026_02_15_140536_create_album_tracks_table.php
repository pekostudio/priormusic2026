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
        Schema::create('album_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('track_number');
            $table->string('name');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('bucket')->nullable();
            $table->text('key')->nullable();
            $table->string('download_token')->nullable();
            $table->string('local_file_path')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('item_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_tracks');
    }
};
