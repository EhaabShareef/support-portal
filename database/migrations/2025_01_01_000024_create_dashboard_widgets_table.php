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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('category', 50); // admin, support, client
            $table->string('base_component', 150); // Base component path (e.g., 'admin.metrics')
            $table->json('available_sizes'); // Array of available sizes ['1x1', '2x2', '3x2']
            $table->string('default_size', 10)->default('2x2');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default_visible')->default(true);
            $table->json('permissions')->nullable(); // Array of required permissions
            $table->json('options')->nullable(); // Widget configuration options
            $table->timestamps();
            
            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};