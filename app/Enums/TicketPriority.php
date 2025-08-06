<?php

namespace App\Enums;

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
        return match ($this) {
            self::LOW => 'text-neutral-700 dark:text-neutral-300',
            self::NORMAL => 'text-sky-700 dark:text-sky-500',
            self::HIGH => 'text-orange-700',
            self::URGENT => 'text-red-600',
            self::CRITICAL => 'bg-red-100 text-red-700 dark:text-red-500',
        };
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
}
