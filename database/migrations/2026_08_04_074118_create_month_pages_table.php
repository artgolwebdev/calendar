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
        Schema::create('month_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->integer('month_number'); // 1-12
            $table->string('font_choice')->default('default');
            $table->string('background_image_path')->nullable();
            $table->string('custom_image_path')->nullable();
            $table->integer('overlay_opacity')->default(30); // 0-100
            $table->timestamps();

            $table->unique(['calendar_id', 'month_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('month_pages');
    }
};
