<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsProgressTracker
{
    private const CACHE_KEY = 'settings_progress_tracker';
    private const CACHE_TTL = 3600; // 1 hour

    private array $modules = [
        'ticket' => [
            'name' => 'Ticket Settings',
            'description' => 'Workflow, statuses, colors, and limits',
            'icon' => 'heroicon-o-ticket',
            'sections' => [
                'workflow' => [
                    'name' => 'Workflow',
                    'description' => 'Ticket workflow and behavior settings',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'attachment' => [
                    'name' => 'Attachments',
                    'description' => 'File upload limits and settings',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'priority' => [
                    'name' => 'Priority Colors',
                    'description' => 'Customize priority color schemes',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'status' => [
                    'name' => 'Status Management',
                    'description' => 'Manage ticket statuses and access',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'general' => [
            'name' => 'General Settings',
            'description' => 'App-wide settings and hotlines',
            'icon' => 'heroicon-o-cog-6-tooth',
            'sections' => [
                'app' => [
                    'name' => 'Application',
                    'description' => 'Basic application settings',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'hotlines' => [
                    'name' => 'Hotlines',
                    'description' => 'Emergency contact numbers',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'organization' => [
            'name' => 'Organization Settings',
            'description' => 'Department groups and departments',
            'icon' => 'heroicon-o-building-office',
            'sections' => [
                'departments' => [
                    'name' => 'Departments',
                    'description' => 'Manage departments',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'groups' => [
                    'name' => 'Department Groups',
                    'description' => 'Manage department groups',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'contracts' => [
            'name' => 'Contract Settings',
            'description' => 'Contract types and statuses',
            'icon' => 'heroicon-o-document-text',
            'sections' => [
                'types' => [
                    'name' => 'Contract Types',
                    'description' => 'Manage contract types',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'statuses' => [
                    'name' => 'Contract Statuses',
                    'description' => 'Manage contract statuses',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'hardware' => [
            'name' => 'Hardware Settings',
            'description' => 'Hardware types and statuses',
            'icon' => 'heroicon-o-computer-desktop',
            'sections' => [
                'types' => [
                    'name' => 'Hardware Types',
                    'description' => 'Manage hardware types',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'statuses' => [
                    'name' => 'Hardware Statuses',
                    'description' => 'Manage hardware statuses',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'schedule' => [
            'name' => 'Schedule Settings',
            'description' => 'Weekend days and event types',
            'icon' => 'heroicon-o-calendar-days',
            'sections' => [
                'weekends' => [
                    'name' => 'Weekend Days',
                    'description' => 'Configure weekend days',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'events' => [
                    'name' => 'Event Types',
                    'description' => 'Manage event types',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
        'users' => [
            'name' => 'User Settings',
            'description' => 'User management defaults',
            'icon' => 'heroicon-o-users',
            'sections' => [
                'defaults' => [
                    'name' => 'User Defaults',
                    'description' => 'Default user settings',
                    'status' => 'pending',
                    'progress' => 0,
                ],
                'permissions' => [
                    'name' => 'Permission Templates',
                    'description' => 'Default permission templates',
                    'status' => 'pending',
                    'progress' => 0,
                ],
            ],
            'overall_progress' => 0,
            'status' => 'pending',
        ],
    ];

    public function __construct()
    {
        $this->loadProgress();
    }

    /**
     * Get all modules with their progress
     */
    public function getAllModules(): array
    {
        return $this->modules;
    }

    /**
     * Get a specific module
     */
    public function getModule(string $key): ?array
    {
        return $this->modules[$key] ?? null;
    }

    /**
     * Update section progress
     */
    public function updateSectionProgress(string $moduleKey, string $sectionKey, int $progress, string $status = null): void
    {
        if (!isset($this->modules[$moduleKey]['sections'][$sectionKey])) {
            return;
        }

        $this->modules[$moduleKey]['sections'][$sectionKey]['progress'] = $progress;
        
        if ($status) {
            $this->modules[$moduleKey]['sections'][$sectionKey]['status'] = $status;
        }

        $this->updateModuleProgress($moduleKey);
        $this->saveProgress();
    }

    /**
     * Update module overall progress
     */
    private function updateModuleProgress(string $moduleKey): void
    {
        if (!isset($this->modules[$moduleKey])) {
            return;
        }

        $sections = $this->modules[$moduleKey]['sections'];
        $totalProgress = 0;
        $completedSections = 0;
        $totalSections = count($sections);

        foreach ($sections as $section) {
            $totalProgress += $section['progress'];
            if ($section['progress'] >= 100) {
                $completedSections++;
            }
        }

        $overallProgress = $totalSections > 0 ? round($totalProgress / $totalSections) : 0;
        
        $this->modules[$moduleKey]['overall_progress'] = $overallProgress;
        
        // Update module status
        if ($overallProgress >= 100) {
            $this->modules[$moduleKey]['status'] = 'completed';
        } elseif ($overallProgress > 0) {
            $this->modules[$moduleKey]['status'] = 'in_progress';
        } else {
            $this->modules[$moduleKey]['status'] = 'pending';
        }
    }

    /**
     * Mark section as completed
     */
    public function markSectionCompleted(string $moduleKey, string $sectionKey): void
    {
        $this->updateSectionProgress($moduleKey, $sectionKey, 100, 'completed');
    }

    /**
     * Mark section as in progress
     */
    public function markSectionInProgress(string $moduleKey, string $sectionKey): void
    {
        $this->updateSectionProgress($moduleKey, $sectionKey, 50, 'in_progress');
    }

    /**
     * Get overall progress across all modules
     */
    public function getOverallProgress(): array
    {
        $totalModules = count($this->modules);
        $completedModules = 0;
        $totalProgress = 0;

        foreach ($this->modules as $module) {
            $totalProgress += $module['overall_progress'];
            if ($module['overall_progress'] >= 100) {
                $completedModules++;
            }
        }

        $overallProgress = $totalModules > 0 ? round($totalProgress / $totalModules) : 0;

        return [
            'overall_progress' => $overallProgress,
            'completed_modules' => $completedModules,
            'total_modules' => $totalModules,
            'status' => $overallProgress >= 100 ? 'completed' : ($overallProgress > 0 ? 'in_progress' : 'pending'),
        ];
    }

    /**
     * Load progress from cache
     */
    private function loadProgress(): void
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            $this->modules = array_merge($this->modules, $cached);
        }
    }

    /**
     * Save progress to cache
     */
    private function saveProgress(): void
    {
        Cache::put(self::CACHE_KEY, $this->modules, self::CACHE_TTL);
    }

    /**
     * Reset progress for a module
     */
    public function resetModuleProgress(string $moduleKey): void
    {
        if (!isset($this->modules[$moduleKey])) {
            return;
        }

        foreach ($this->modules[$moduleKey]['sections'] as $sectionKey => $section) {
            $this->modules[$moduleKey]['sections'][$sectionKey]['progress'] = 0;
            $this->modules[$moduleKey]['sections'][$sectionKey]['status'] = 'pending';
        }

        $this->updateModuleProgress($moduleKey);
        $this->saveProgress();
    }

    /**
     * Reset all progress
     */
    public function resetAllProgress(): void
    {
        foreach ($this->modules as $moduleKey => $module) {
            $this->resetModuleProgress($moduleKey);
        }
    }
}
