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
        Schema::create('organization_hardware', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique()->nullable(); // Internal asset tracking
            
            $table->foreignId('organization_id')
                  ->constrained('organizations')
                  ->onDelete('cascade');
                  
            $table->foreignId('contract_id')
                  ->nullable()
                  ->constrained('organization_contracts')
                  ->onDelete('set null'); // Hardware can exist without contract
            
            $table->foreignId('hardware_type_id')
                  ->nullable()
                  ->constrained('hardware_types')
                  ->onDelete('set null'); // Modern relational approach
            
            $table->string('hardware_type')->nullable()->index(); // Fallback for legacy data
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('serial_required')->default(false);
            $table->string('serial_number')->unique()->nullable();
            
            $table->date('purchase_date')->nullable();
            $table->string('location')->nullable(); // Physical location
            $table->text('remarks')->nullable();
            
            $table->timestamp('last_maintenance')->nullable();
            $table->timestamp('next_maintenance')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['organization_id', 'hardware_type']);
            $table->index('next_maintenance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_hardware');
    }
};