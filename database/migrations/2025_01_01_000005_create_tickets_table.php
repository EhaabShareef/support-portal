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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ticket ID
            $table->string('ticket_number')->unique(); // Human-readable ticket number
            $table->string('subject')->index(); // Indexed for search
            
            $table->enum('status', [
                'open',
                'in_progress',
                'awaiting_customer_response',
                'awaiting_case_closure',
                'sales_engagement',
                'monitoring',
                'solution_provided', 
                'closed',
                'on_hold'
            ])->default('open')->index(); // Indexed for filtering
            
            $table->enum('priority', [
                'low',
                'normal', 
                'high',
                'urgent',
                'critical'
            ])->default('normal')->index(); // Indexed for filtering
            
            $table->text('description')->nullable(); // Initial ticket description
            
            // Foreign keys with proper naming and constraints
            $table->foreignId('organization_id')
                  ->constrained('organizations')
                  ->onDelete('cascade'); // If org deleted, tickets should be deleted
                  
            $table->foreignId('client_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // If client deleted, their tickets go too
                  
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('restrict'); // Prevent department deletion if has tickets
                  
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); // If agent deleted, unassign ticket
            
            // Tracking fields
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('response_time_minutes')->nullable(); // For SLA tracking
            $table->integer('resolution_time_minutes')->nullable(); // For SLA tracking
            
            $table->timestamps();
            $table->softDeletes(); // Important for audit trails
            
            // Composite indexes for common queries
            $table->index(['organization_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['priority', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('ticket_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};