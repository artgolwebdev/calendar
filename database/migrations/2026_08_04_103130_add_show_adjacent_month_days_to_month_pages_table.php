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
            $table->boolean('show_adjacent_month_days')->default(true)->after('day_box_bg_opacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_pages', function (Blueprint $table) {
            $table->dropColumn('show_adjacent_month_days');
        });
    }
};
