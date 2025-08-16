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
        Schema::create('organization_subscription_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // trial, active, suspended, cancelled
            $table->string('label'); // Display name
            $table->string('color', 20)->default('#3b82f6'); // hex color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_active', 'sort_order']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_subscription_statuses');
    }
};