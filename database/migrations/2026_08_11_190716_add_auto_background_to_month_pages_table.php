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
            $table->foreignId('auto_background_media_id')->nullable()
                ->after('background_media_id')
                ->constrained('media')
                ->nullOnDelete();
            $table->foreignId('auto_background_family_member_id')->nullable()
                ->after('auto_background_media_id')
                ->constrained('family_members')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auto_background_family_member_id');
            $table->dropConstrainedForeignId('auto_background_media_id');
        });
    }
};
