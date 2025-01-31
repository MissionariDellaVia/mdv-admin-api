<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gospels', function (Blueprint $table) {
            $table->id('gospel_id');
            $table->string('gospel_verse')->unique();
            $table->text('gospel_text');
            $table->string('evangelist', 100);
            $table->text('sacred_text_reference')->nullable();
            $table->string('liturgical_period', 100)->nullable();
            $table->unsignedInteger('latest_comment_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique('gospel_verse', 'idx_gospel_verse');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gospels');
    }
};
