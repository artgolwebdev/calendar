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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('birth_date');
            $table->date('anniversary_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('hobbies')->nullable();
            $table->json('favorite_sports')->nullable();
            $table->json('favorite_music')->nullable();
            $table->json('favorite_food')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
