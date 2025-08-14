<?php

namespace App\Livewire\Admin\Reports;

use App\Models\User;
use App\Models\UserActivity;
use Livewire\Component;
use Livewire\WithPagination;

class UserActivityReport extends Component
{
    use WithPagination;

    public $userIds = [];
    public $activityTypes = [];
    public $actions = [];
    public $startDate;
    public $endDate;
    public $keyword = '';

    protected $queryString = [
        'userIds', 'activityTypes', 'actions', 'startDate', 'endDate', 'keyword'
    ];

    public function mount(): void
    {
        if (!auth()->user()->can('reports.read')) {
            abort(403);
        }
        $this->startDate = $this->startDate ?: now()->subDays(30)->format('Y-m-d');
        $this->endDate = $this->endDate ?: now()->format('Y-m-d');
    }

    public function getActivitiesProperty()
    {
        $query = UserActivity::query()->with('user');
        $query->when($this->userIds, fn($q) => $q->whereIn('user_id', $this->userIds));
        $query->when($this->activityTypes, fn($q) => $q->whereIn('activity_type', $this->activityTypes));
        $query->when($this->actions, fn($q) => $q->whereIn('action', $this->actions));
        $query->whereBetween('created_at', [
            \Carbon\Carbon::parse($this->startDate)->startOfDay(),
            \Carbon\Carbon::parse($this->endDate)->endOfDay()
        ]);
        $query->when($this->keyword, fn($q) => $q->where('message', 'like', '%'.$this->keyword.'%'));

        return $query->latest()->paginate(25);
    }

    public function export()
    {
        app(\App\Services\ActivityLogger::class)->log(auth()->user(), 'reports', 'exported', null, 'Exported user activity log');
        // Placeholder export; integrate CSV later
        session()->flash('message', 'Export started.');
    }

    public function render()
    {
        return view('livewire.admin.reports.user-activity-report', [
            'activities' => $this->activities,
            'users' => User::orderBy('name')->get(),
        ])->title('User Activity Log');
    }
}
