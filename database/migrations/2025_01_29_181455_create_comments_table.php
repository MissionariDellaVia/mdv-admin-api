<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->foreignId('gospel_id')->constrained('gospels', 'gospel_id')->onDelete('cascade');
            $table->text('comment_text');
            $table->text('extra_info')->nullable();
            $table->string('youtube_link', 255)->nullable();
            $table->integer('comment_order');
            $table->boolean('is_latest')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['gospel_id', 'is_latest'], 'idx_gospel_comments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
