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
            $table->string('weekday_color')->nullable()->after('day_box_font_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_pages', function (Blueprint $table) {
            $table->dropColumn('weekday_color');
        });
    }
};
