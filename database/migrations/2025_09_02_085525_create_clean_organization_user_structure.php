<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Create the organization_users pivot table (only for client users)
        Schema::create('organization_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Unique constraints
            $table->unique(['user_id', 'organization_id'], 'unique_user_org');

            // Indexes for performance
            $table->index('user_id', 'idx_organization_users_user');
            $table->index('organization_id', 'idx_organization_users_org');
            $table->index(['organization_id', 'is_primary'], 'idx_organization_users_primary');
        });

        // Step 2: Add foreign key constraint for primary_user_id in organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('primary_user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Step 3: Seed the data
        $this->seedOrganizationUserData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint first
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['primary_user_id']);
        });

        Schema::dropIfExists('organization_users');
    }

    /**
     * Seed the organization-user relationships (only for client users)
     */
    private function seedOrganizationUserData(): void
    {
        // Get all existing users and organizations
        $users = DB::table('users')->get();
        $organizations = DB::table('organizations')->get();

        // Create organization-user relationships only for client users
        foreach ($organizations as $org) {
            // Find users that belong to this organization (from the old relationship)
            $orgUsers = $users->where('organization_id', $org->id);
            
            if ($orgUsers->count() > 0) {
                foreach ($orgUsers as $user) {
                    // Create the relationship in the pivot table
                    DB::table('organization_users')->insert([
                        'user_id' => $user->id,
                        'organization_id' => $org->id,
                        'is_primary' => false, // Will be set in next step
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Set the first user as primary
                $firstUser = $orgUsers->first();
                DB::table('organization_users')
                    ->where('user_id', $firstUser->id)
                    ->where('organization_id', $org->id)
                    ->update(['is_primary' => true]);

                // Update organization with primary user
                DB::table('organizations')
                    ->where('id', $org->id)
                    ->update(['primary_user_id' => $firstUser->id]);
            }
        }

        // Update all existing users to 'standard' type (only client users need this)
        DB::table('users')->update(['user_type' => 'standard']);
    }
};
