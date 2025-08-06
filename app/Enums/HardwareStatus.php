<?php

namespace App\Enums;

enum HardwareStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';
    case DISPOSED = 'disposed';
    case LOST = 'lost';

    /**
     * Get the human-readable label for the hardware status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MAINTENANCE => 'Maintenance',
            self::RETIRED => 'Retired',
            self::DISPOSED => 'Disposed',
            self::LOST => 'Lost',
        };
    }

    /**
     * Get all hardware statuses as an array for form options.
     */
    public static function toArray(): array
    {
        return array_map(
            fn(HardwareStatus $status) => [
                'value' => $status->value,
                'label' => $status->label()
            ],
            self::cases()
        );
    }

    /**
     * Get all hardware statuses as a simple key-value array.
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn(HardwareStatus $status) => $status->value, self::cases()),
            array_map(fn(HardwareStatus $status) => $status->label(), self::cases())
        );
    }

    /**
     * Get the CSS classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            self::MAINTENANCE => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            self::RETIRED => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            self::DISPOSED => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
            self::LOST => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        };
    }
}