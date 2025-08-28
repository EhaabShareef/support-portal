<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_organization_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_hardware_id')->constrained('organization_hardware')->cascadeOnDelete();
            $table->text('maintenance_note')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'organization_hardware_id']);
            $table->index('organization_hardware_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_organization_hardware');
    }
};

