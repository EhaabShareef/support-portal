<?php

namespace App\Enums;

enum HardwareType: string
{
    case DESKTOP = 'desktop';
    case LAPTOP = 'laptop';
    case SERVER = 'server';
    case PRINTER = 'printer';
    case SCANNER = 'scanner';
    case ROUTER = 'router';
    case SWITCH = 'switch';
    case FIREWALL = 'firewall';
    case STORAGE = 'storage';
    case MONITOR = 'monitor';
    case PROJECTOR = 'projector';
    case PHONE = 'phone';
    case TABLET = 'tablet';
    case OTHER = 'other';

    /**
     * Get the human-readable label for the hardware type.
     */
    public function label(): string
    {
        return match ($this) {
            self::DESKTOP => 'Desktop',
            self::LAPTOP => 'Laptop',
            self::SERVER => 'Server',
            self::PRINTER => 'Printer',
            self::SCANNER => 'Scanner',
            self::ROUTER => 'Router',
            self::SWITCH => 'Switch',
            self::FIREWALL => 'Firewall',
            self::STORAGE => 'Storage',
            self::MONITOR => 'Monitor',
            self::PROJECTOR => 'Projector',
            self::PHONE => 'Phone',
            self::TABLET => 'Tablet',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get all hardware types as an array for form options.
     */
    public static function toArray(): array
    {
        return array_map(
            fn(HardwareType $type) => [
                'value' => $type->value,
                'label' => $type->label()
            ],
            self::cases()
        );
    }

    /**
     * Get all hardware types as a simple key-value array.
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn(HardwareType $type) => $type->value, self::cases()),
            array_map(fn(HardwareType $type) => $type->label(), self::cases())
        );
    }
}