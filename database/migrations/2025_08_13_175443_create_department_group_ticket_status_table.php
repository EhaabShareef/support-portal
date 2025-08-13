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
        Schema::create('department_group_ticket_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_status_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['department_group_id', 'ticket_status_id'], 'dept_group_ticket_status_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_group_ticket_status');
    }
};
