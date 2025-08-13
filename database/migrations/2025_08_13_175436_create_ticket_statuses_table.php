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
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name
            $table->string('key')->unique(); // Internal identifier (open, in_progress, closed)
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6b7280'); // Hex color code
            $table->integer('sort_order')->default(0);
            $table->boolean('is_protected')->default(false); // Cannot be deleted/edited
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_statuses');
    }
};
