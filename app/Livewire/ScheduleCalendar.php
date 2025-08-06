<?php

namespace App\Livewire;

use App\Models\DepartmentGroup;
use App\Models\Schedule;
use App\Models\ScheduleEventType;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ScheduleCalendar extends Component
{
    public $currentDate;
    public $selectedDepartmentGroup = '';
    public $selectedEventType = '';

    // Schedule event creation properties
    public bool $showScheduleModal = false;
    public bool $scheduleEditMode = false;
    public ?int $selectedScheduleId = null;
    public $scheduleForm = [
        'user_id' => '',
        'event_type_id' => '',
        'start_date' => '',
        'end_date' => '',
        'remarks' => '',
    ];

    public function mount()
    {
        // Check permissions - only Admin and Client can access
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin', 'Client'])) {
            abort(403, 'You do not have permission to access the schedule module.');
        }

        $this->currentDate = now();
        
        // Initialize modal states
        $this->showScheduleModal = false;
        $this->scheduleEditMode = false;
        $this->selectedScheduleId = null;
    }

    public function previousMonth()
    {
        $this->currentDate = $this->currentDate->subMonth();
    }

    public function nextMonth()
    {
        $this->currentDate = $this->currentDate->addMonth();
    }

    public function goToToday()
    {
        $this->currentDate = now();
    }

    #[Computed]
    public function monthName()
    {
        return $this->currentDate->format('F Y');
    }

    #[Computed]
    public function daysInMonth()
    {
        return $this->currentDate->daysInMonth;
    }

    #[Computed]
    public function departmentGroups()
    {
        return DepartmentGroup::active()
            ->with(['activeDepartments'])
            ->ordered()
            ->get();
    }

    #[Computed]
    public function eventTypes()
    {
        return ScheduleEventType::active()->ordered()->get();
    }

    #[Computed]
    public function users()
    {
        $user = auth()->user();
        $query = User::query()
            ->with(['department:id,name,department_group_id', 'department.departmentGroup:id,name'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'Admin', 'Agent']);
            });

        // Apply role-based filtering
        if ($user->hasRole('Client')) {
            // Clients can only see users from their organization's departments
            $query->whereHas('department', function ($q) use ($user) {
                $q->whereHas('tickets', function ($ticketQ) use ($user) {
                    $ticketQ->where('organization_id', $user->organization_id);
                });
            });
        }

        // Apply department group filter
        if ($this->selectedDepartmentGroup) {
            $query->whereHas('department', function ($q) {
                $q->where('department_group_id', $this->selectedDepartmentGroup);
            });
        }

        return $query->orderBy('name')->get()
            ->groupBy('department.departmentGroup.name');
    }

    #[Computed]
    public function schedules()
    {
        $user = auth()->user();
        $year = $this->currentDate->year;
        $month = $this->currentDate->month;

        $query = Schedule::with([
            'user:id,name,department_id',
            'user.department:id,name,department_group_id',
            'eventType:id,code,label,color'
        ])->overlapsMonth($year, $month);

        // Apply role-based filtering
        if ($user->hasRole('Client')) {
            // Clients can only see schedules for their organization's users
            $query->whereHas('user', function ($q) use ($user) {
                $q->whereHas('department', function ($deptQ) use ($user) {
                    $deptQ->whereHas('tickets', function ($ticketQ) use ($user) {
                        $ticketQ->where('organization_id', $user->organization_id);
                    });
                });
            });
        }

        // Apply filters
        if ($this->selectedDepartmentGroup) {
            $query->forDepartmentGroup($this->selectedDepartmentGroup);
        }

        if ($this->selectedEventType) {
            $query->withEventType($this->selectedEventType);
        }

        return $query->get();
    }


    #[Computed]
    public function allUsers()
    {
        return User::with(['department:id,name'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'Admin', 'Agent']);
            })->orderBy('name')->get();
    }


    public function getSchedulesForUserAndDay($userId, $day)
    {
        try {
            $schedulesCollection = $this->schedules;
            
            // Return empty collection if no schedules
            if (!$schedulesCollection || $schedulesCollection->isEmpty()) {
                return collect();
            }
            
            // Filter schedules for the specific user and day
            return $schedulesCollection->filter(function ($schedule) use ($userId, $day) {
                // Check if this schedule belongs to the user
                if ($schedule->user_id !== $userId) {
                    return false;
                }
                
                // Check if this schedule spans the given day
                return $schedule->spansDay($day, $this->currentDate->year, $this->currentDate->month);
            });
            
        } catch (\Exception $e) {
            // Log error and return empty collection
            logger('Error in getSchedulesForUserAndDay: ' . $e->getMessage());
            return collect();
        }
    }

    // Schedule event creation methods
    public function createScheduleEvent()
    {
        // Check permissions - only Admin can create events
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            session()->flash('error', 'You do not have permission to create schedule events.');
            return;
        }

        // Reset and prepare form
        $this->resetScheduleForm();
        $this->scheduleEditMode = false;
        $this->showScheduleModal = true;

        // Set default dates
        $this->scheduleForm['start_date'] = $this->currentDate->format('Y-m-d');
        $this->scheduleForm['end_date'] = $this->currentDate->format('Y-m-d');
        
        // Set default event type (SO - Office Support)
        $defaultEventType = ScheduleEventType::where('code', 'SO')->first();
        if ($defaultEventType) {
            $this->scheduleForm['event_type_id'] = $defaultEventType->id;
        }
    }

    public function saveScheduleEvent()
    {
        // Check permissions
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            session()->flash('error', 'You do not have permission to save schedule events.');
            return;
        }

        $validated = $this->validate([
            'scheduleForm.user_id' => 'required|exists:users,id',
            'scheduleForm.event_type_id' => 'required|exists:schedule_event_types,id',
            'scheduleForm.start_date' => 'required|date',
            'scheduleForm.end_date' => 'required|date|after_or_equal:scheduleForm.start_date',
            'scheduleForm.remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $scheduleData = $validated['scheduleForm'];
            $userId = $scheduleData['user_id'];
            $startDate = $scheduleData['start_date'];
            $endDate = $scheduleData['end_date'];

            // Check for overlapping events for the same user (one event per user per time period)
            $overlappingQuery = Schedule::where('user_id', $userId)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                });

            // Exclude current record if editing
            if ($this->scheduleEditMode) {
                $overlappingQuery->where('id', '!=', $this->selectedScheduleId);
            }

            $overlapping = $overlappingQuery->first();

            if ($overlapping) {
                session()->flash('error', 'This user already has a scheduled event during this time period. Please choose different dates or edit the existing event.');
                return;
            }

            // Prepare data for saving
            $scheduleData['created_by'] = auth()->id();
            $scheduleData['date'] = $scheduleData['start_date']; // For backward compatibility

            if ($this->scheduleEditMode) {
                Schedule::findOrFail($this->selectedScheduleId)->update($scheduleData);
                session()->flash('message', 'Schedule event updated successfully.');
            } else {
                Schedule::create($scheduleData);
                session()->flash('message', 'Schedule event created successfully.');
            }

            $this->closeScheduleModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save schedule event: ' . $e->getMessage());
        }
    }

    public function closeScheduleModal()
    {
        $this->showScheduleModal = false;
        $this->resetScheduleForm();
    }

    private function resetScheduleForm()
    {
        $this->scheduleForm = [
            'user_id' => '',
            'event_type_id' => '',
            'start_date' => '',
            'end_date' => '',
            'remarks' => '',
        ];
        $this->selectedScheduleId = null;
        $this->resetErrorBag('scheduleForm');
    }

    public function render()
    {
        return view('livewire.schedule-calendar')
            ->title('Schedule Calendar - ' . $this->monthName);
    }
}
