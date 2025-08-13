<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hardware_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_hardware_id')->constrained('organization_hardware')->onDelete('cascade');
            $table->string('serial');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['organization_hardware_id', 'serial']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hardware_serials');
    }
};
