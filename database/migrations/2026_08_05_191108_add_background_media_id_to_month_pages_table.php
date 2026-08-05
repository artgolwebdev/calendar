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
        Schema::table('month_pages', function (Blueprint $table) {
            $table->unsignedBigInteger('background_media_id')->nullable()->after('background_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_pages', function (Blueprint $table) {
            $table->dropColumn('background_media_id');
        });
    }
};
