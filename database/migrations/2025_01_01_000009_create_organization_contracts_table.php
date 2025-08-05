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
        Schema::create('organization_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique(); // Human-readable contract number
            
            $table->foreignId('organization_id')
                  ->constrained('organizations')
                  ->onDelete('cascade');
                  
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('restrict'); // Don't delete department if has contracts
            
            $table->enum('type', ['support', 'hardware', 'software', 'consulting', 'maintenance'])
                  ->default('support');
                  
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])
                  ->default('draft')
                  ->index();
            
            $table->boolean('includes_hardware')->default(false);
            $table->decimal('contract_value', 10, 2)->nullable(); // Contract monetary value
            $table->string('currency', 3)->default('USD'); // Currency code
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('renewal_months')->nullable(); // Auto-renewal period
            
            $table->text('csi_remarks')->nullable(); // Customer Service Index remarks
            $table->text('terms_conditions')->nullable(); // Contract terms
            $table->json('service_levels')->nullable(); // SLA definitions
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('contract_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_contracts');
    }
};