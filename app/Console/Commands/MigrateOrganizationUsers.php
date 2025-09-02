<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateOrganizationUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizations:migrate-users {--dry-run : Show what would be migrated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing user-organization relationships to new pivot table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Organization-User Relationship Migration...');
        
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }
        
        // Check if old organization_id column exists
        if (!Schema::hasColumn('users', 'organization_id')) {
            $this->error('❌ Old organization_id column not found. Migration may have already been completed.');
            return 1;
        }
        
        try {
            // Step 1: Migrate existing relationships
            $this->migrateExistingRelationships();
            
            // Step 2: Set primary users
            $this->setPrimaryUsers();
            
            // Step 3: Update user types
            $this->updateUserTypes();
            
            // Step 4: Remove old organization_id column (only if not dry-run)
            if (!$this->option('dry-run')) {
                $this->removeOldOrganizationIdColumn();
            }
            
            $this->info('✅ Migration completed successfully!');
            
            if ($this->option('dry-run')) {
                $this->warn('🔍 This was a dry run. Run without --dry-run to apply changes.');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Step 1: Migrate existing user-organization relationships to pivot table
     */
    private function migrateExistingRelationships()
    {
        $this->info('📊 Step 1: Migrating existing user-organization relationships...');
        
        $users = User::whereNotNull('organization_id')->get();
        $count = 0;
        
        $this->info("Found {$users->count()} users with organization assignments");
        
        foreach ($users as $user) {
            // Check if relationship already exists
            $exists = DB::table('organization_users')
                ->where('user_id', $user->id)
                ->where('organization_id', $user->organization_id)
                ->exists();
                
            if (!$exists) {
                if (!$this->option('dry-run')) {
                    DB::table('organization_users')->insert([
                        'user_id' => $user->id,
                        'organization_id' => $user->organization_id,
                        'is_primary' => false, // Will be set in next step
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                $count++;
            }
        }
        
        $this->info("✅ Migrated {$count} relationships to pivot table");
    }
    
    /**
     * Step 2: Set primary users for each organization
     */
    private function setPrimaryUsers()
    {
        $this->info('👑 Step 2: Setting primary users for organizations...');
        
        $organizations = Organization::all();
        $count = 0;
        
        foreach ($organizations as $org) {
            $firstUser = DB::table('organization_users')
                ->where('organization_id', $org->id)
                ->first();
                
            if ($firstUser) {
                if (!$this->option('dry-run')) {
                    // Update pivot table
                    DB::table('organization_users')
                        ->where('id', $firstUser->id)
                        ->update(['is_primary' => true]);
                        
                    // Update organization table
                    $org->update(['primary_user_id' => $firstUser->user_id]);
                }
                $count++;
                
                $this->line("  - {$org->name}: User ID {$firstUser->user_id} set as primary");
            } else {
                $this->warn("  - {$org->name}: No users found to set as primary");
            }
        }
        
        $this->info("✅ Set primary users for {$count} organizations");
    }
    
    /**
     * Step 3: Update all users to 'standard' type
     */
    private function updateUserTypes()
    {
        $this->info('👥 Step 3: Updating user types...');
        
        $userCount = User::count();
        
        if (!$this->option('dry-run')) {
            User::query()->update(['user_type' => 'standard']);
        }
        
        $this->info("✅ Updated {$userCount} users to 'standard' type");
    }
    
    /**
     * Step 4: Remove old organization_id column from users table
     */
    private function removeOldOrganizationIdColumn()
    {
        $this->info('🗑️ Step 4: Removing old organization_id column...');
        
        if (Schema::hasColumn('users', 'organization_id')) {
            // Drop foreign key constraint first
            $foreignKeys = $this->getForeignKeys('users', 'organization_id');
            foreach ($foreignKeys as $foreignKey) {
                Schema::table('users', function ($table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                });
            }
            
            // Drop the column
            Schema::table('users', function ($table) {
                $table->dropColumn('organization_id');
            });
            
            $this->info('✅ Removed old organization_id column');
        } else {
            $this->info('ℹ️ organization_id column already removed');
        }
    }
    
    /**
     * Get foreign key constraints for a column
     */
    private function getForeignKeys($table, $column)
    {
        $foreignKeys = [];
        
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$table}' 
            AND COLUMN_NAME = '{$column}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($constraints as $constraint) {
            $foreignKeys[] = $constraint->CONSTRAINT_NAME;
        }
        
        return $foreignKeys;
    }
}
