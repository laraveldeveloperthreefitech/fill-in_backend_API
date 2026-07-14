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
        Schema::create('filter_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name like "Name"
            $table->string('key');  // Internal key like "name"
            $table->enum('type', ['text', 'select', 'number', 'date']); // Input field type
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For select/dropdowns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_attributes');
    }
};
