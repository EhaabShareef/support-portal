<?php

namespace App\Livewire\Dashboard\Widgets\Client\MyTickets;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Small extends Component
{
    public $ticketData = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;

    public function mount(): void
    {
        // Check permissions before loading data
        $user = Auth::user();
        if (!$user || !$user->can('dashboard.client')) {
            abort(403, 'Insufficient permissions to view this widget.');
        }
        
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            $organizationId = $user->organization_id;
            
            if (!$organizationId) {
                throw new \Exception('User not associated with an organization');
            }
            
            $this->ticketData = Cache::remember("client_tickets_small_{$user->id}", 300, function () use ($organizationId) {
                return [
                    'total_tickets' => Ticket::where('organization_id', $organizationId)->count(),
                    'open_tickets' => Ticket::where('organization_id', $organizationId)
                        ->whereNotIn('status', ['closed', 'resolved'])
                        ->count(),
                    'resolved_tickets' => Ticket::where('organization_id', $organizationId)
                        ->whereIn('status', ['closed', 'resolved'])
                        ->count(),
                    'recent_ticket' => Ticket::where('organization_id', $organizationId)
                        ->latest()
                        ->first(),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Client My Tickets Small widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("client_tickets_small_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.client.my-tickets.small');
    }
}