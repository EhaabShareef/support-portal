<?php

namespace App\Helpers;

/**
 * Custom autoloader to handle Flysystem class conflicts
 * This prevents the "Ambiguous class resolution" error when both
 * league/flysystem and league/flysystem-local packages are installed
 */
class FlysystemAutoloader
{
    /**
     * Register the custom autoloader
     */
    public static function register(): void
    {
        // Only register if there are conflicts
        if (self::hasConflicts()) {
            spl_autoload_register([self::class, 'autoload'], true, true);
            
            // Log the conflict for debugging
            \Log::info('Flysystem class conflicts detected and autoloader registered', self::getConflictInfo());
        }
    }

    /**
     * Custom autoloader function
     */
    public static function autoload(string $class): void
    {
        // Only handle Flysystem Local classes
        if (strpos($class, 'League\\Flysystem\\Local\\') !== 0) {
            return;
        }

        // Check if the class already exists (loaded from main flysystem package)
        if (class_exists($class, false)) {
            return;
        }

        // Define the path to the flysystem-local package
        $flysystemLocalPath = base_path('vendor/league/flysystem-local');
        
        if (!is_dir($flysystemLocalPath)) {
            return;
        }

        // Map class names to file paths
        $classMap = [
            'League\\Flysystem\\Local\\FallbackMimeTypeDetector' => $flysystemLocalPath . '/FallbackMimeTypeDetector.php',
            'League\\Flysystem\\Local\\LocalFilesystemAdapter' => $flysystemLocalPath . '/LocalFilesystemAdapter.php',
        ];

        // Load the class if it's in our map and the file exists
        if (isset($classMap[$class]) && file_exists($classMap[$class])) {
            // Check if the class is already loaded from the main flysystem package
            if (!class_exists($class, false)) {
                require_once $classMap[$class];
            }
        }
    }

    /**
     * Check if there are conflicting classes
     */
    public static function hasConflicts(): bool
    {
        $flysystemLocalPath = base_path('vendor/league/flysystem-local');
        $flysystemPath = base_path('vendor/league/flysystem');
        
        return is_dir($flysystemLocalPath) && is_dir($flysystemPath);
    }

    /**
     * Get information about the conflict
     */
    public static function getConflictInfo(): array
    {
        $flysystemLocalPath = base_path('vendor/league/flysystem-local');
        $flysystemPath = base_path('vendor/league/flysystem');
        
        return [
            'has_conflict' => self::hasConflicts(),
            'flysystem_local_exists' => is_dir($flysystemLocalPath),
            'flysystem_exists' => is_dir($flysystemPath),
            'flysystem_local_classes' => is_dir($flysystemLocalPath) ? self::getClassesInDirectory($flysystemLocalPath) : [],
            'flysystem_classes' => is_dir($flysystemPath) ? self::getClassesInDirectory($flysystemPath) : [],
        ];
    }

    /**
     * Safely remove the conflicting flysystem-local package
     * This should only be called after ensuring the main flysystem package
     * contains all necessary classes
     */
    public static function removeConflictingPackage(): bool
    {
        $flysystemLocalPath = base_path('vendor/league/flysystem-local');
        
        if (!is_dir($flysystemLocalPath)) {
            return true; // Already removed
        }

        try {
            // Check if main flysystem package has the required classes
            if (!self::mainPackageHasRequiredClasses()) {
                \Log::warning('Cannot remove flysystem-local package - main package missing required classes');
                return false;
            }

            // Remove the directory
            self::removeDirectory($flysystemLocalPath);
            
            \Log::info('Successfully removed conflicting flysystem-local package');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Failed to remove flysystem-local package', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if the main flysystem package has the required classes
     */
    private static function mainPackageHasRequiredClasses(): bool
    {
        $requiredClasses = [
            'League\\Flysystem\\Local\\FallbackMimeTypeDetector',
            'League\\Flysystem\\Local\\LocalFilesystemAdapter'
        ];

        foreach ($requiredClasses as $class) {
            if (!class_exists($class, false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recursively remove a directory
     */
    private static function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                self::removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        rmdir($path);
    }

    /**
     * Get all PHP classes in a directory
     */
    private static function getClassesInDirectory(string $directory): array
    {
        $classes = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                    $classes[] = $matches[1];
                }
            }
        }

        return $classes;
    }
}
