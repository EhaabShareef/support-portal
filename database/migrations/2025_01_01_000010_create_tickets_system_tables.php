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
        // Create tickets table with all enhancements
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ticket ID
            $table->string('ticket_number')->unique(); // Human-readable ticket number
            $table->string('subject')->index(); // Indexed for search
            
            $table->string('status', 50)->default('open')->index(); // Indexed for filtering
            
            $table->enum('priority', [
                'low',
                'normal', 
                'high',
                'urgent',
                'critical'
            ])->default('normal')->index(); // Indexed for filtering
            
            $table->boolean('critical_confirmed')->default(false)->index(); // For critical priority confirmation workflow
            
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
                  
            $table->foreignId('owner_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); // If owner deleted, unassign ticket

            // Split functionality fields
            $table->foreignId('split_from_ticket_id')->nullable();
            // Explicitly name self-referencing FK to avoid driver naming issues
            $table->foreign('split_from_ticket_id', 'fk_tickets_split_from')
                  ->references('id')
                  ->on('tickets')
                  ->nullOnDelete(); // For split ticket tracking

            // Merge functionality fields
            $table->boolean('is_merged')->default(false)->index();
            $table->foreignId('merged_into_ticket_id')->nullable();
            // Explicitly name self-referencing FK to avoid driver naming issues
            $table->foreign('merged_into_ticket_id', 'fk_tickets_merged_into')
                  ->references('id')
                  ->on('tickets')
                  ->nullOnDelete();
            $table->boolean('is_merged_master')->default(false)->index();
            
            // Tracking fields
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('latest_message_at')->nullable(); // For sorting tickets by latest activity
            $table->integer('response_time_minutes')->nullable(); // For SLA tracking
            $table->integer('resolution_time_minutes')->nullable(); // For SLA tracking
            
            $table->timestamps();
            $table->softDeletes(); // Important for audit trails
            
            // Composite indexes for common queries with named indexes for better management
            $table->index(['organization_id', 'status'], 'idx_tickets_org_status');
            $table->index(['department_id', 'status'], 'idx_tickets_dept_status');
            $table->index(['owner_id', 'status'], 'idx_tickets_owner_status');
            $table->index(['priority', 'status'], 'idx_tickets_priority_status');
            $table->index(['created_at', 'status'], 'idx_tickets_created_status');
            $table->index('ticket_number', 'idx_tickets_number');
            
            // Additional composite indexes for common filtering patterns
            $table->index(['organization_id', 'status', 'created_at'], 'idx_tickets_org_status_created');
            $table->index(['owner_id', 'department_id'], 'idx_tickets_owner_dept');
            
            // Single column indexes for common queries
            $table->index('status', 'idx_tickets_status');
            $table->index('priority', 'idx_tickets_priority');
            $table->index('owner_id', 'idx_tickets_owner_id');
            $table->index('created_at', 'idx_tickets_created_at');
        });

        // Create ticket_messages table
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(false)->index();
            $table->boolean('is_system_message')->default(false)->index();
            $table->boolean('is_log')->default(false)->index(); // For system logs
            $table->json('metadata')->nullable(); // For additional data
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['ticket_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['is_internal', 'created_at']);
        });

        // Create ticket_notes table  
        Schema::create('ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('color')->default('sky'); // For note color coding
            $table->boolean('is_internal')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ticket_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Create ticket_cc_recipients table
        Schema::create('ticket_cc_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('email');
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate CC emails per ticket
            $table->unique(['ticket_id', 'email']);
            $table->index(['ticket_id', 'active']);
        });

        // Create ticket_message_attachments table with all enhancements
        Schema::create('ticket_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For secure download URLs
            $table->foreignId('ticket_message_id')->constrained('ticket_messages')->cascadeOnDelete();
            $table->string('disk')->default('local'); // Private storage by default for security
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable(); // For proper Content-Type headers
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['ticket_message_id', 'created_at']);
            $table->index('uuid'); // For download lookups
        });

        // ticket_hardware pivot table moved to later migration (after organization_hardware exists)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_hardware');
        Schema::dropIfExists('ticket_message_attachments');
        Schema::dropIfExists('ticket_cc_recipients');
        Schema::dropIfExists('ticket_notes');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
    }
};
