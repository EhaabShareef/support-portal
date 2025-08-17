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

        return TicketStatusModel::optionsForDepartmentGroup($departmentGroupId);
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
     * Check if a status key is valid (either core enum or in database)
     */
    public static function isValidKey(string $value): bool
    {
        // Check if it's a core enum case
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return true;
            }
        }

        // Check if it exists in database
        return TicketStatusModel::where('key', $value)->active()->exists();
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
