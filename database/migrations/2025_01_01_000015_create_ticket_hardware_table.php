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
        // Runs after organization_hardware is created (same timestamp but filename orders later)
        Schema::create('ticket_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('organization_hardware_id')->constrained('organization_hardware')->cascadeOnDelete();
            $table->text('maintenance_note')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('fixed')->default(false);
            $table->timestamps();

            $table->unique(['ticket_id', 'organization_hardware_id'], 'ticket_hardware_unique');
            $table->index('organization_hardware_id');
            $table->index('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_hardware');
    }
};

