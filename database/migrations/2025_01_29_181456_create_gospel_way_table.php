<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gospel_way', function (Blueprint $table) {
            $table->id('gospel_way_id');
            $table->date('calendar_date');
            $table->foreignId('gospel_id')->constrained('gospels', 'gospel_id');
            $table->foreignId('saint_id')->nullable()->constrained('saints', 'saint_id');
            $table->string('liturgical_season', 100)->nullable();
            $table->boolean('is_solemnity')->default(false);
            $table->boolean('is_feast')->default(false);
            $table->boolean('is_memorial')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique('calendar_date', 'idx_calendar_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gospel_way');
    }
};
