<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table): void {
            $table->index('bpm');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('CREATE INDEX tracks_genre_filter_index ON tracks (genre(191))');

            return;
        }

        Schema::table('tracks', function (Blueprint $table): void {
            $table->index('genre', 'tracks_genre_filter_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table): void {
            $table->dropIndex('tracks_genre_filter_index');
        });

        Schema::table('tracks', function (Blueprint $table): void {
            $table->dropIndex(['bpm']);
        });
    }
};
