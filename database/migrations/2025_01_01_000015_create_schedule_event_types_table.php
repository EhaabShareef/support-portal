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
        Schema::create('schedule_event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Changed from 'code' to 'name'
            $table->text('description')->nullable(); // Added description field
            $table->string('color', 20)->default('#3b82f6'); // hex color with default
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_active', 'sort_order']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_event_types');
    }
};