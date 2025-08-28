<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_hardware_serial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hardware_serial_id')->constrained('hardware_serials')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_id', 'hardware_serial_id']);
            $table->index('hardware_serial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_hardware_serial');
    }
};

