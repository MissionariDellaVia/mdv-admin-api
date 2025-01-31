<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saints', function (Blueprint $table) {
            $table->id('saint_id');
            $table->string('name');
            $table->text('biography')->nullable();
            $table->date('recurrence_date')->nullable();
            $table->date('feast_day')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('recurrence_date', 'idx_saint_recurrence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saints');
    }
};
