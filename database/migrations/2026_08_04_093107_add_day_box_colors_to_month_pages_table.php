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
            $table->string('day_box_bg_color')->nullable()->after('overlay_opacity');
            $table->string('day_box_font_color')->nullable()->after('day_box_bg_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_pages', function (Blueprint $table) {
            $table->dropColumn(['day_box_bg_color', 'day_box_font_color']);
        });
    }
};
