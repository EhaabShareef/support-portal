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
        Schema::create('user_widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->string('size', 10); // Selected size (must be in widget's available_sizes)
            $table->integer('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'widget_id']);
            $table->index(['user_id', 'is_visible', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_widget_settings');
    }
};