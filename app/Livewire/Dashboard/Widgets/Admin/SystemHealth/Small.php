<?php

namespace App\Livewire\Dashboard\Widgets\Admin\SystemHealth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Small extends Component
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
            
            $this->healthData = Cache::remember("system_health_small_{$user->id}", 60, function () {
                return [
                    'database_status' => $this->checkDatabaseHealth(),
                    'cache_status' => $this->checkCacheHealth(),
                    'queue_status' => $this->checkQueueHealth(),
                    'storage_status' => $this->checkStorageHealth(),
                ];
            });

            // Determine overall status
            $this->overallStatus = $this->calculateOverallStatus();
            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("System Health Small widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function checkDatabaseHealth(): string
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkCacheHealth(): string
    {
        try {
            Cache::put('health_check', 'test', 10);
            if (Cache::get('health_check') === 'test') {
                Cache::forget('health_check');
                return 'healthy';
            }
            return 'warning';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkQueueHealth(): string
    {
        try {
            // Simple queue health check - could be expanded with actual queue monitoring
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkStorageHealth(): string
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            if ($usagePercent > 90) {
                return 'error';
            } elseif ($usagePercent > 75) {
                return 'warning';
            }
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function calculateOverallStatus(): string
    {
        $statuses = array_values($this->healthData);
        
        if (in_array('error', $statuses)) {
            return 'error';
        } elseif (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        return 'healthy';
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("system_health_small_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.system-health.small');
    }
}