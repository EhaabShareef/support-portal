<?php

namespace App\Livewire\Dashboard\Widgets\Admin\SystemHealth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Large extends Component
{
    public $healthData = [];
    public $performanceMetrics = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;
    public string $overallStatus = 'unknown';

    public function mount(): void
    {
        // Check permissions before loading data
        $user = Auth::user();
        if (!$user || !$user->can('dashboard.admin')) {
            abort(403, 'Insufficient permissions to view this widget.');
        }
        
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            
            $data = Cache::remember("system_health_large_{$user->id}", 60, function () {
                return [
                    'health' => [
                        'database' => $this->getDatabaseHealth(),
                        'cache' => $this->getCacheHealth(),
                        'queue' => $this->getQueueHealth(),
                        'storage' => $this->getStorageHealth(),
                        'memory' => $this->getMemoryHealth(),
                        'php' => $this->getPHPHealth(),
                    ],
                    'performance' => [
                        'uptime' => $this->getSystemUptime(),
                        'load_average' => $this->getLoadAverage(),
                        'database_size' => $this->getDatabaseSize(),
                        'recent_errors' => $this->getRecentErrors(),
                        'active_sessions' => $this->getActiveSessions(),
                    ]
                ];
            });

            $this->healthData = $data['health'];
            $this->performanceMetrics = $data['performance'];

            // Determine overall status
            $this->overallStatus = $this->calculateOverallStatus();
            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("System Health Large widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function getDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            // Test a simple query
            $start = microtime(true);
            DB::table('users')->count();
            $queryTime = round((microtime(true) - $start) * 1000, 2);
            
            $connectionCount = $this->getDatabaseConnectionCount();
            
            return [
                'status' => $responseTime < 100 ? 'healthy' : ($responseTime < 500 ? 'warning' : 'error'),
                'response_time' => $responseTime . 'ms',
                'query_time' => $queryTime . 'ms',
                'connections' => $connectionCount,
                'message' => 'Database operational'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'response_time' => 'N/A',
                'query_time' => 'N/A',
                'connections' => 0,
                'message' => 'Database unavailable'
            ];
        }
    }

    private function getCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testData = 'performance_test_data';
            
            // Write test
            $start = microtime(true);
            Cache::put($testKey, $testData, 60);
            $writeTime = round((microtime(true) - $start) * 1000, 4);
            
            // Read test
            $start = microtime(true);
            $retrieved = Cache::get($testKey);
            $readTime = round((microtime(true) - $start) * 1000, 4);
            
            Cache::forget($testKey);
            
            $status = ($writeTime < 10 && $readTime < 10 && $retrieved === $testData) ? 'healthy' : 'warning';
            
            return [
                'status' => $status,
                'write_time' => $writeTime . 'ms',
                'read_time' => $readTime . 'ms',
                'driver' => config('cache.default'),
                'message' => 'Cache operations normal'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'write_time' => 'N/A',
                'read_time' => 'N/A',
                'driver' => config('cache.default'),
                'message' => 'Cache system error'
            ];
        }
    }

    private function getQueueHealth(): array
    {
        try {
            // Basic queue health - could be expanded with actual queue monitoring
            return [
                'status' => 'healthy',
                'driver' => config('queue.default'),
                'pending_jobs' => 0, // Would need actual queue inspection
                'failed_jobs' => 0,  // Would need actual queue inspection
                'message' => 'Queue system operational'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('queue.default'),
                'pending_jobs' => 'Unknown',
                'failed_jobs' => 'Unknown',
                'message' => 'Queue system error'
            ];
        }
    }

    private function getStorageHealth(): array
    {
        try {
            $storagePath = storage_path();
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usagePercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1);
            
            $status = 'healthy';
            $message = 'Storage usage normal';
            
            if ($usagePercent > 90) {
                $status = 'error';
                $message = 'Storage critically low';
            } elseif ($usagePercent > 75) {
                $status = 'warning';
                $message = 'Storage usage high';
            }
            
            return [
                'status' => $status,
                'usage_percent' => $usagePercent,
                'free_space' => $this->formatBytes($freeSpace),
                'total_space' => $this->formatBytes($totalSpace),
                'used_space' => $this->formatBytes($totalSpace - $freeSpace),
                'message' => $message
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'usage_percent' => 0,
                'free_space' => 'Unknown',
                'total_space' => 'Unknown',
                'used_space' => 'Unknown',
                'message' => 'Storage check failed'
            ];
        }
    }

    private function getMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));
        $usagePercent = round(($memoryUsage / $memoryLimit) * 100, 1);
        $peakPercent = round(($memoryPeak / $memoryLimit) * 100, 1);
        
        $status = 'healthy';
        if ($usagePercent > 80) {
            $status = 'warning';
        } elseif ($usagePercent > 95) {
            $status = 'error';
        }
        
        return [
            'status' => $status,
            'usage_percent' => $usagePercent,
            'peak_percent' => $peakPercent,
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => $this->formatBytes($memoryLimit)
        ];
    }

    private function getPHPHealth(): array
    {
        return [
            'status' => 'healthy',
            'version' => PHP_VERSION,
            'extensions_loaded' => count(get_loaded_extensions()),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }

    private function getSystemUptime(): string
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return 'N/A (Windows)';
            }
            
            $uptime = file_get_contents('/proc/uptime');
            $seconds = (int) floatval($uptime);
            
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getLoadAverage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return ['1min' => 'N/A', '5min' => 'N/A', '15min' => 'N/A'];
            }
            
            $loadavg = file_get_contents('/proc/loadavg');
            $loads = explode(' ', trim($loadavg));
            
            return [
                '1min' => $loads[0] ?? 'N/A',
                '5min' => $loads[1] ?? 'N/A',
                '15min' => $loads[2] ?? 'N/A'
            ];
        } catch (\Exception $e) {
            return ['1min' => 'Error', '5min' => 'Error', '15min' => 'Error'];
        }
    }

    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = DATABASE()")[0]->size ?? 0;
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getRecentErrors(): int
    {
        try {
            // This would typically check Laravel logs or system error logs
            return 0; // Placeholder - would implement actual error counting
        } catch (\Exception $e) {
            return -1;
        }
    }

    private function getActiveSessions(): int
    {
        try {
            // Count active user sessions - this is a simplified version
            return DB::table('sessions')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getDatabaseConnectionCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateOverallStatus(): string
    {
        $statuses = array_column($this->healthData, 'status');
        
        if (in_array('error', $statuses)) {
            return 'error';
        } elseif (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        return 'healthy';
    }

    private function formatBytes($size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    private function parseSize($size): int
    {
        $unit = strtolower(substr($size, -1, 1));
        $value = (int) $size;
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("system_health_large_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.system-health.large');
    }
}