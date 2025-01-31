<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->default('9999-01-01');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('place', 255);
            $table->boolean('is_holy_mass')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern', 50)->nullable();
            $table->foreignId('address_id')->nullable()->constrained('addresses', 'address_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
