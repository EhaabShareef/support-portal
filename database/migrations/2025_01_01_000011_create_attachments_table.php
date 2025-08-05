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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ID for downloads
            
            // Polymorphic relationship - can attach to tickets, knowledge articles, etc.
            $table->morphs('attachable');
            
            $table->string('original_name'); // Original filename
            $table->string('stored_name'); // Stored filename (for security)
            $table->string('path'); // Storage path
            $table->string('disk')->default('local'); // Storage disk
            $table->string('mime_type');
            $table->bigInteger('size'); // File size in bytes
            $table->string('extension', 10);
            
            $table->boolean('is_public')->default(false); // Public vs private files
            $table->boolean('is_image')->default(false); // Quick image check
            $table->json('metadata')->nullable(); // Image dimensions, etc.
            
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (attachable_type/attachable_id index is auto-created by morphs())
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['mime_type', 'is_public']);
            $table->index('extension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};