<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case AWAITING_CUSTOMER_RESPONSE = 'awaiting_customer_response';
    case AWAITING_CASE_CLOSURE = 'awaiting_case_closure';
    case SALES_ENGAGEMENT = 'sales_engagement';
    case MONITORING = 'monitoring';
    case SOLUTION_PROVIDED = 'solution_provided';
    case CLOSED = 'closed';
    case ON_HOLD = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::AWAITING_CUSTOMER_RESPONSE => 'Awaiting Customer Response',
            self::AWAITING_CASE_CLOSURE => 'Awaiting Case Closure',
            self::SALES_ENGAGEMENT => 'Sales Engagement',
            self::MONITORING => 'Monitoring',
            self::SOLUTION_PROVIDED => 'Solution Provided',
            self::CLOSED => 'Closed',
            self::ON_HOLD => 'On Hold',
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::OPEN => 'bg-lime-100 text-lime-700',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-700',
            self::AWAITING_CUSTOMER_RESPONSE => 'bg-yellow-100 text-yellow-700',
            self::AWAITING_CASE_CLOSURE => 'bg-cyan-100 text-cyan-700',
            self::SALES_ENGAGEMENT => 'bg-pink-100 text-pink-700',
            self::MONITORING => 'bg-teal-100 text-teal-700',
            self::SOLUTION_PROVIDED => 'bg-emerald-100 text-emerald-700',
            self::CLOSED => 'bg-red-100 text-red-700',
            self::ON_HOLD => 'bg-neutral-100 text-neutral-700',
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
