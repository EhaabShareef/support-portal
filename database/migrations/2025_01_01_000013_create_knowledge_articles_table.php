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
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ID
            $table->string('title')->index(); // Indexed for search
            $table->string('slug')->unique()->index(); // SEO-friendly URLs
            $table->text('excerpt')->nullable(); // Short description
            $table->longText('content'); // Article content
            
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft')
                  ->index();
                  
            $table->boolean('is_featured')->default(false); // Featured articles
            $table->integer('view_count')->default(0); // Track popularity
            $table->integer('helpful_count')->default(0); // User feedback
            $table->integer('not_helpful_count')->default(0); // User feedback
            
            $table->json('tags')->nullable(); // Article tags for categorization
            $table->json('metadata')->nullable(); // SEO metadata, etc.
            
            // Foreign keys
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict'); // Don't allow deletion of users with articles
                  
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
                  
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('set null'); // Articles can be department-specific
            
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Full-text search index (MySQL/PostgreSQL)
            $table->fullText(['title', 'content']);
            
            // Composite indexes
            $table->index(['status', 'published_at']);
            $table->index(['department_id', 'status']);
            $table->index(['is_featured', 'status']);
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};