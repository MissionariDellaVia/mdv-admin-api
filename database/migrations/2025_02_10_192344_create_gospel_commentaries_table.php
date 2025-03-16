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
        Schema::create('gospel_commentaries', function (Blueprint $table) {
            $table->id('commentary_id');
            $table->date('calendar_date');
            $table->foreignId('gospel_id')->constrained('gospels', 'gospel_id');
            $table->foreignId('saint_id')->nullable()->constrained('saints', 'saint_id');
            $table->text('comment_text');
            $table->text('extra_info')->nullable();
            $table->string('youtube_link', 255)->nullable();
            $table->string('liturgical_season', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique('calendar_date', 'idx_calendar_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gospel_commentaries');
    }
};
