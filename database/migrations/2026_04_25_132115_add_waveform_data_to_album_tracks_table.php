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
        Schema::table('album_tracks', function (Blueprint $table) {
            $table->json('waveform_peaks')->nullable()->after('item_type');
            $table->string('waveform_version')->nullable()->after('waveform_peaks');
            $table->timestamp('waveform_generated_at')->nullable()->after('waveform_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('album_tracks', function (Blueprint $table) {
            $table->dropColumn([
                'waveform_peaks',
                'waveform_version',
                'waveform_generated_at',
            ]);
        });
    }
};
