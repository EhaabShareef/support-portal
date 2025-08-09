<?php

namespace App\Livewire\Dashboard\Widgets\Admin\SystemHealth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Medium extends Component
{
    public $healthData = [];
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
            
            $this->healthData = Cache::remember("system_health_medium_{$user->id}", 60, function () {
                return [
                    'database' => $this->getDatabaseHealth(),
                    'cache' => $this->getCacheHealth(),
                    'queue' => $this->getQueueHealth(),
                    'storage' => $this->getStorageHealth(),
                    'memory' => $this->getMemoryHealth(),
                    'uptime' => $this->getSystemUptime(),
                ];
            });

            // Determine overall status
            $this->overallStatus = $this->calculateOverallStatus();
            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("System Health Medium widget failed to load", [
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
            
            return [
                'status' => 'healthy',
                'response_time' => $responseTime . 'ms',
                'message' => 'Database connection active'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'response_time' => 'N/A',
                'message' => 'Database connection failed'
            ];
        }
    }

    private function getCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            
            if (Cache::get($testKey) === 'test') {
                Cache::forget($testKey);
                return [
                    'status' => 'healthy',
                    'message' => 'Cache operations working',
                    'driver' => config('cache.default')
                ];
            }
            
            return [
                'status' => 'warning',
                'message' => 'Cache read/write issues',
                'driver' => config('cache.default')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache system unavailable',
                'driver' => config('cache.default')
            ];
        }
    }

    private function getQueueHealth(): array
    {
        try {
            return [
                'status' => 'healthy',
                'message' => 'Queue system operational',
                'driver' => config('queue.default')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue system issues',
                'driver' => config('queue.default')
            ];
        }
    }

    private function getStorageHealth(): array
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
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
                'message' => $message
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'usage_percent' => 0,
                'free_space' => 'Unknown',
                'message' => 'Storage check failed'
            ];
        }
    }

    private function getMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));
        $usagePercent = round(($memoryUsage / $memoryLimit) * 100, 1);
        
        $status = 'healthy';
        if ($usagePercent > 80) {
            $status = 'warning';
        } elseif ($usagePercent > 95) {
            $status = 'error';
        }
        
        return [
            'status' => $status,
            'usage_percent' => $usagePercent,
            'current' => $this->formatBytes($memoryUsage),
            'limit' => $this->formatBytes($memoryLimit)
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

    private function calculateOverallStatus(): string
    {
        $statuses = [
            $this->healthData['database']['status'],
            $this->healthData['cache']['status'],
            $this->healthData['queue']['status'],
            $this->healthData['storage']['status'],
            $this->healthData['memory']['status'],
        ];
        
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
        Cache::forget("system_health_medium_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.system-health.medium');
    }
}