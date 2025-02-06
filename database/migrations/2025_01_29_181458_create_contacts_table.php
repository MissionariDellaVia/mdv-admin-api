<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id('contact_id');
            $table->enum('contact_type', ['Email', 'Phone', 'Fax', 'Other']);
            $table->string('contact_group', 50)->nullable();
            $table->string('contact_type_description', 50)->nullable();
            $table->string('contact_value', 150);
            $table->foreignId('place_id')->nullable()->constrained('places', 'place_id');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
