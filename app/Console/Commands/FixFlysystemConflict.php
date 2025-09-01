<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\FlysystemAutoloader;

class FixFlysystemConflict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:flysystem-conflict {--force : Force removal without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Flysystem class conflicts by removing redundant packages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking for Flysystem class conflicts...');
        
        if (!FlysystemAutoloader::hasConflicts()) {
            $this->info('✅ No Flysystem conflicts detected. Your system is clean!');
            return Command::SUCCESS;
        }

        $this->warn('⚠️  Flysystem class conflicts detected!');
        
        $conflictInfo = FlysystemAutoloader::getConflictInfo();
        $this->table(
            ['Conflict Status', 'Value'],
            [
                ['Has Conflict', $conflictInfo['has_conflict'] ? 'Yes' : 'No'],
                ['Flysystem Local Exists', $conflictInfo['flysystem_local_exists'] ? 'Yes' : 'No'],
                ['Main Flysystem Exists', $conflictInfo['flysystem_exists'] ? 'Yes' : 'No'],
            ]
        );

        if ($conflictInfo['flysystem_local_classes']) {
            $this->warn('Classes in flysystem-local package:');
            foreach ($conflictInfo['flysystem_local_classes'] as $class) {
                $this->line("  - {$class}");
            }
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to attempt to fix this conflict by removing the redundant package?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🔧 Attempting to fix the conflict...');
        
        try {
            $success = FlysystemAutoloader::removeConflictingPackage();
            
            if ($success) {
                $this->info('✅ Successfully removed conflicting package!');
                $this->info('💡 You may need to run "composer dump-autoload" to refresh the autoloader.');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Failed to remove conflicting package. Check the logs for details.');
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
