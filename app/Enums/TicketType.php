<?php

namespace App\Enums;

enum TicketType: string
{
    case ISSUE = 'issue';
    case FEEDBACK = 'feedback';
    case BUG = 'bug';
    case LEAD = 'lead';
    case TASK = 'task';
    case INCIDENT = 'incident';
    case REQUEST = 'request';

    public function label(): string
    {
        return match ($this) {
            self::ISSUE => 'Issue',
            self::FEEDBACK => 'Feedback',
            self::BUG => 'Bug',
            self::LEAD => 'Lead',
            self::TASK => 'Task',
            self::INCIDENT => 'Incident',
            self::REQUEST => 'Request',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ISSUE => 'heroicon-o-exclamation-circle',
            self::FEEDBACK => 'heroicon-o-chat-bubble-left-right',
            self::BUG => 'heroicon-o-bug-ant',
            self::LEAD => 'heroicon-o-user-plus',
            self::TASK => 'heroicon-o-clipboard-document-check',
            self::INCIDENT => 'heroicon-o-shield-exclamation',
            self::REQUEST => 'heroicon-o-hand-raised',
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::ISSUE => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
            self::FEEDBACK => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::BUG => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            self::LEAD => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
            self::TASK => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
            self::INCIDENT => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            self::REQUEST => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
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