<?php

namespace App\Livewire\Dashboard\Widgets\Support\AgentContributions;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class Small extends Component
{
    public $heatmapData = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can('dashboard.support')) {
            abort(403, 'Insufficient permissions to view this widget.');
        }
        
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            $today = now();
            $startDate = $today->copy()->subDays(6);
            
            $this->heatmapData = Cache::remember("agent_contrib_small_{$user->id}_{$startDate->format('Y-m-d')}", 300, function () use ($user, $startDate, $today) {
                $contributions = $this->getContributionData($user->id, $startDate, $today);
                
                $heatmap = [];
                for ($i = 0; $i < 7; $i++) {
                    $date = $startDate->copy()->addDays($i);
                    $dateStr = $date->format('Y-m-d');
                    $heatmap[] = [
                        'date' => $dateStr,
                        'day' => $date->format('D'),
                        'count' => $contributions[$dateStr] ?? 0
                    ];
                }
                
                return $heatmap;
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Agent Contributions Small widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function getContributionData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $ticketCreations = DB::table('tickets')
            ->select(DB::raw('DATE(created_at) as date'), 'id as ticket_id')
            ->where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $ticketUpdates = DB::table('ticket_messages')
            ->select(DB::raw('DATE(created_at) as date'), 'ticket_id')
            ->where('sender_id', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $ticketClosures = DB::table('tickets')
            ->select(DB::raw('DATE(COALESCE(resolved_at, closed_at)) as date'), 'id as ticket_id')
            ->where('owner_id', $userId)
            ->where(function($q) {
                $q->whereNotNull('resolved_at')->orWhereNotNull('closed_at');
            })
            ->whereBetween(DB::raw('DATE(COALESCE(resolved_at, closed_at))'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $allEvents = collect();
        
        foreach ($ticketCreations as $event) {
            $allEvents->push(['date' => $event->date, 'ticket_id' => $event->ticket_id]);
        }
        
        foreach ($ticketUpdates as $event) {
            $allEvents->push(['date' => $event->date, 'ticket_id' => $event->ticket_id]);
        }
        
        foreach ($ticketClosures as $event) {
            $allEvents->push(['date' => $event->date, 'ticket_id' => $event->ticket_id]);
        }

        return $allEvents
            ->groupBy('date')
            ->map(function ($events) {
                return $events->pluck('ticket_id')->unique()->count();
            })
            ->toArray();
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("agent_contrib_small_" . Auth::id() . "_" . now()->subDays(6)->format('Y-m-d'));
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.support.agent-contributions.small');
    }
}