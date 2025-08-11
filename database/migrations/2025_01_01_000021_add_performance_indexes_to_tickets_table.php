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
        Schema::table('tickets', function (Blueprint $table) {
            // Index for common filtering by status
            $table->index('status', 'idx_tickets_status');
            
            // Index for priority filtering
            $table->index('priority', 'idx_tickets_priority');
            
            // Composite index for agent ticket queries (department + status)
            $table->index(['department_id', 'status'], 'idx_tickets_dept_status');
            
            // Composite index for client ticket queries (organization + status)
            $table->index(['organization_id', 'status'], 'idx_tickets_org_status');
            
            // Index for assigned tickets queries
            $table->index('assigned_to', 'idx_tickets_assigned_to');
            
            // Composite index for unassigned tickets filtering
            $table->index(['assigned_to', 'department_id'], 'idx_tickets_unassigned_dept');
            
            // Index for creation date ordering (most common sort)
            $table->index('created_at', 'idx_tickets_created_at');
            
            // Index for ticket number searches (unique lookups)
            $table->index('ticket_number', 'idx_tickets_number');
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            // Index for ticket message queries
            $table->index(['ticket_id', 'created_at'], 'idx_ticket_messages_ticket_created');
            
            // Index for sender message queries
            $table->index('sender_id', 'idx_ticket_messages_sender');
        });

        Schema::table('ticket_notes', function (Blueprint $table) {
            // Index for ticket notes queries
            $table->index(['ticket_id', 'created_at'], 'idx_ticket_notes_ticket_created');
            
            // Index for user notes queries
            $table->index('user_id', 'idx_ticket_notes_user');
            
            // Index for internal/external notes filtering
            $table->index('is_internal', 'idx_ticket_notes_internal');
        });

        Schema::table('users', function (Blueprint $table) {
            // Index for department-based user queries (for agent filtering)
            $table->index('department_id', 'idx_users_department');
            
            // Index for organization-based user queries (for client filtering)
            $table->index('organization_id', 'idx_users_organization');
            
            // Index for active user filtering
            $table->index('is_active', 'idx_users_active');
        });

        Schema::table('departments', function (Blueprint $table) {
            // Index for department group queries
            $table->index('department_group_id', 'idx_departments_group');
            
            // Index for active department filtering
            $table->index('is_active', 'idx_departments_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_status');
            $table->dropIndex('idx_tickets_priority');
            $table->dropIndex('idx_tickets_dept_status');
            $table->dropIndex('idx_tickets_org_status');
            $table->dropIndex('idx_tickets_assigned_to');
            $table->dropIndex('idx_tickets_unassigned_dept');
            $table->dropIndex('idx_tickets_created_at');
            $table->dropIndex('idx_tickets_number');
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_messages_ticket_created');
            $table->dropIndex('idx_ticket_messages_sender');
        });

        Schema::table('ticket_notes', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_notes_ticket_created');
            $table->dropIndex('idx_ticket_notes_user');
            $table->dropIndex('idx_ticket_notes_internal');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_department');
            $table->dropIndex('idx_users_organization');
            $table->dropIndex('idx_users_active');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_group');
            $table->dropIndex('idx_departments_active');
        });
    }
};
