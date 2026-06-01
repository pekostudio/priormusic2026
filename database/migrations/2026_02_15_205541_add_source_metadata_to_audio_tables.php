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
        Schema::table('libraries', function (Blueprint $table) {
            $table->json('source_metadata')->nullable()->after('type');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->json('source_metadata')->nullable()->after('libraryfeatured');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->json('source_metadata')->nullable()->after('api_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn('source_metadata');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('source_metadata');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('source_metadata');
        });
    }
};
