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
            
            $table->string('hardware_type')->index(); // Server, Desktop, Laptop, etc.
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->string('specifications')->nullable(); // CPU, RAM, Storage, etc.
            
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('warranty_start')->nullable();
            $table->date('warranty_expiration')->nullable(); // For warranty tracking
            
            $table->enum('status', ['active', 'maintenance', 'retired', 'disposed', 'lost'])
                  ->default('active')
                  ->index();
                  
            $table->string('location')->nullable(); // Physical location
            $table->text('remarks')->nullable();
            $table->json('custom_fields')->nullable(); // Flexible additional data
            
            $table->timestamp('last_maintenance')->nullable();
            $table->timestamp('next_maintenance')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['organization_id', 'status']);
            $table->index(['hardware_type', 'status']);
            $table->index(['warranty_expiration']);
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