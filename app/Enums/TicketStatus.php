<?php

namespace App\Enums;

use App\Services\TicketColorService;
use App\Models\TicketStatus as TicketStatusModel;

enum TicketStatus: string
{
    // Core protected statuses that always exist
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';

    public function label(): string
    {
        // Try to get dynamic label from database first
        $statusModel = TicketStatusModel::where('key', $this->value)->first();
        if ($statusModel) {
            return $statusModel->name;
        }

        // Fallback to static labels
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress', 
            self::CLOSED => 'Closed',
        };
    }

    public function cssClass(): string
    {
        $colorService = app(TicketColorService::class);
        return $colorService->getStatusClasses($this->value);
    }

    /**
     * Get all available statuses (dynamic from database)
     */
    public static function values(): array
    {
        return TicketStatusModel::active()->pluck('key')->toArray();
    }

    /**
     * Get all available status options (dynamic from database)
     */
    public static function options(): array
    {
        return TicketStatusModel::active()
            ->ordered()
            ->pluck('name', 'key')
            ->toArray();
    }

    /**
     * Get status options for a specific department group
     */
    public static function optionsForDepartmentGroup(?int $departmentGroupId): array
    {
        if (!$departmentGroupId) {
            return self::options();
        }

        return TicketStatusModel::active()
            ->forDepartmentGroup($departmentGroupId)
            ->ordered()
            ->pluck('name', 'key')
            ->toArray();
    }

    /**
     * Get validation rule for status
     */
    public static function validationRule(?int $departmentGroupId = null): string
    {
        $validStatuses = $departmentGroupId 
            ? array_keys(self::optionsForDepartmentGroup($departmentGroupId))
            : self::values();
            
        return 'required|in:' . implode(',', $validStatuses);
    }

    /**
     * Create enum instance from string (with fallback)
     */
    public static function tryFrom(string $value): ?self
    {
        // Try to match against core enum cases first
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        // If not a core case, check if it exists in database
        if (TicketStatusModel::where('key', $value)->exists()) {
            // For dynamic statuses, we'll handle them as strings in the application
            return null;
        }

        return null;
    }

    /**
     * Get color for a status key (works with both enum and dynamic statuses)
     */
    public static function getColorForKey(string $key): string
    {
        $statusModel = TicketStatusModel::where('key', $key)->first();
        return $statusModel ? $statusModel->color : '#6b7280';
    }

    /**
     * Get name for a status key (works with both enum and dynamic statuses)
     */
    public static function getNameForKey(string $key): string
    {
        $statusModel = TicketStatusModel::where('key', $key)->first();
        return $statusModel ? $statusModel->name : ucfirst(str_replace('_', ' ', $key));
    }
}
