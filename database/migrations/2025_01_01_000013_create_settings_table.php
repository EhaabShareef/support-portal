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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index(); // Setting identifier
            $table->text('value')->nullable(); // Setting value (JSON, string, etc.)
            $table->string('type')->default('string'); // string, json, boolean, integer
            $table->string('group')->default('general')->index(); // Setting group/category
            $table->string('label'); // Human-readable label
            $table->text('description')->nullable(); // Setting description
            $table->boolean('is_public')->default(false); // Can be accessed by frontend
            $table->boolean('is_encrypted')->default(false); // Should be encrypted
            $table->json('validation_rules')->nullable(); // Validation rules
            $table->timestamps();
            
            // Indexes
            $table->index(['group', 'is_public']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};