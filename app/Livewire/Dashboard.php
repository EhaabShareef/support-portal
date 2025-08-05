<?php

namespace App\Livewire;

use App\Enums\TicketStatus;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Dashboard statistic cards.
     */
    public array $stats = [];

    /**
     * Ticket trend data for the chart component.
     */
    public array $ticketTrends = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user?->can('tickets.view')) {
            $this->stats['open'] = Ticket::where('status', TicketStatus::OPEN->value)->count();

            $this->stats['resolvedToday'] = Ticket::where('status', TicketStatus::CLOSED->value)
                ->whereDate('updated_at', today())
                ->count();

            $this->ticketTrends = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();
        }

        if ($user?->can('organizations.view')) {
            $this->stats['organizations'] = Organization::count();
        }

        if ($user?->can('users.view')) {
            $this->stats['activeUsers'] = User::where('active_yn', true)->count();
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
