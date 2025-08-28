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
        Schema::create('ticket_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_hardware_id')->constrained('organization_hardware')->cascadeOnDelete();
            $table->text('maintenance_note')->nullable();
            $table->timestamps();

            // Ensure one hardware item per ticket (can be modified if multiple hardware items per ticket is needed)
            $table->unique(['ticket_id', 'organization_hardware_id'], 'ticket_hardware_unique');
            
            // Indexes for performance
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

