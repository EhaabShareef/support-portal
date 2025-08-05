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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // Indexed for search
            $table->text('description')->nullable();
            $table->foreignId('department_group_id')
                  ->nullable()
                  ->constrained('department_groups')
                  ->onDelete('set null');
            $table->string('email')->nullable(); // Department contact email
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['department_group_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};