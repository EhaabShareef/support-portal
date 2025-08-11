<?php

namespace App\Enums;

use App\Services\TicketColorService;

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
        $colorService = app(TicketColorService::class);
        return $colorService->getStatusClasses($this->value);
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
