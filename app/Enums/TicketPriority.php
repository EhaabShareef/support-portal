<?php

namespace App\Enums;

use App\Services\TicketColorService;

enum TicketPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
            self::CRITICAL => 'Critical',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOW => 'heroicon-o-arrow-down-circle',
            self::NORMAL => 'heroicon-o-arrow-path',
            self::HIGH => 'heroicon-o-arrow-up-circle',
            self::URGENT => 'heroicon-o-exclamation-triangle',
            self::CRITICAL => 'heroicon-o-fire',
        };
    }

    public function cssClass(): string
    {
        $colorService = app(TicketColorService::class);
        return $colorService->getPriorityClasses($this->value);
    }

    public function displayText(): string
    {
        return match ($this) {
            self::LOW => 'LOW',
            self::NORMAL => 'NORMAL',
            self::HIGH => 'HIGH',
            self::URGENT => 'URGENT',
            self::CRITICAL => 'CRITICAL',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }

    public static function validationRule(): string
    {
        return 'required|in:' . implode(',', self::values());
    }

    /**
     * Get the numeric value for priority comparison.
     */
    public function getValue(): int
    {
        return match ($this) {
            self::LOW => 1,
            self::NORMAL => 2,
            self::HIGH => 3,
            self::URGENT => 4,
            self::CRITICAL => 5,
        };
    }

    /**
     * Compare two priorities.
     * Returns:
     *  - negative if $priority1 < $priority2
     *  - 0 if $priority1 == $priority2
     *  - positive if $priority1 > $priority2
     */
    public static function compare(string $priority1, string $priority2): int
    {
        $p1Value = self::from($priority1)->getValue();
        $p2Value = self::from($priority2)->getValue();
        
        return $p1Value - $p2Value;
    }
}
