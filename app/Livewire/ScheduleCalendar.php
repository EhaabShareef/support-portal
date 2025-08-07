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
        // Check permissions - admin and client can access
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'client'])) {
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
                $q->whereIn('name', ['admin', 'support']);
            });

        // Apply role-based filtering
        if ($user->hasRole('client')) {
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
            'user:id,name,department_id,organization_id',
            'user.department:id,name,department_group_id',
            'eventType:id,code,label,color'
        ])->overlapsMonth($year, $month);

        // Apply role-based filtering
        if ($user->hasRole('client')) {
            // Improved client filtering using direct organization relationship
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
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
    public function schedulesGroupedByUserAndDay()
    {
        $schedulesGrouped = [];
        
        foreach ($this->schedules as $schedule) {
            $userId = $schedule->user_id;
            
            // Calculate which days this schedule spans
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                if ($schedule->spansDay($day, $this->currentDate->year, $this->currentDate->month)) {
                    if (!isset($schedulesGrouped[$userId][$day])) {
                        $schedulesGrouped[$userId][$day] = collect();
                    }
                    $schedulesGrouped[$userId][$day]->push($schedule);
                }
            }
        }
        
        return $schedulesGrouped;
    }


    #[Computed]
    public function allUsers()
    {
        return User::with(['department:id,name'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'support']);
            })->orderBy('name')->get();
    }


    public function getSchedulesForUserAndDay($userId, $day)
    {
        try {
            $groupedSchedules = $this->schedulesGroupedByUserAndDay;
            
            // Return schedules for this user and day, or empty collection if none exist
            return $groupedSchedules[$userId][$day] ?? collect();
            
        } catch (\Exception $e) {
            // Log error and return empty collection
            logger('Error in getSchedulesForUserAndDay: ' . $e->getMessage());
            return collect();
        }
    }

    // Helper methods for spanning events
    public function getEventsStartingOnDay($userId, $day)
    {
        try {
            $groupedSchedules = $this->schedulesGroupedByUserAndDay;
            $daySchedules = $groupedSchedules[$userId][$day] ?? collect();
            
            return $daySchedules->filter(function ($schedule) use ($day) {
                // Check if this is the start day of the event in current month
                if ($schedule->start_date && $schedule->end_date) {
                    $currentMonth = $this->currentDate->month;
                    $currentYear = $this->currentDate->year;
                    
                    // If event starts in current month, check if it's the exact start day
                    if ($schedule->start_date->year === $currentYear && $schedule->start_date->month === $currentMonth) {
                        return $schedule->start_date->day === (int) $day;
                    }
                    
                    // If event started before current month, it should start from day 1 of current month
                    if ($schedule->start_date->lessThan($this->currentDate->copy()->startOfMonth())) {
                        return $day === 1;
                    }
                }
                
                return false;
            });
            
        } catch (\Exception $e) {
            logger('Error in getEventsStartingOnDay: ' . $e->getMessage());
            return collect();
        }
    }

    public function getEventColspan($schedule, $day)
    {
        try {
            if (!$schedule->start_date || !$schedule->end_date) {
                return 1;
            }
            
            $currentMonth = $this->currentDate->month;
            $currentYear = $this->currentDate->year;
            $daysInMonth = $this->daysInMonth;
            
            // Calculate start day in current month
            $startDay = $day;
            if ($schedule->start_date->year === $currentYear && $schedule->start_date->month === $currentMonth) {
                $startDay = max($day, $schedule->start_date->day);
            } else if ($schedule->start_date->lessThan($this->currentDate->copy()->startOfMonth())) {
                $startDay = 1; // Event started before current month
            }
            
            // Calculate end day in current month
            $endDay = $daysInMonth;
            if ($schedule->end_date->year === $currentYear && $schedule->end_date->month === $currentMonth) {
                $endDay = min($daysInMonth, $schedule->end_date->day);
            } else if ($schedule->end_date->greaterThan($this->currentDate->copy()->endOfMonth())) {
                $endDay = $daysInMonth; // Event extends beyond current month
            }
            
            $colspan = max(1, $endDay - $startDay + 1);
            
            // Ensure colspan doesn't exceed remaining days in month from current day
            $maxColspan = $daysInMonth - $day + 1;
            $colspan = min($colspan, $maxColspan);
            
            return $colspan;
            
        } catch (\Exception $e) {
            logger('Error in getEventColspan: ' . $e->getMessage());
            return 1;
        }
    }

    public function isDayCoveredBySpanningEvent($userId, $day)
    {
        try {
            $schedulesCollection = $this->schedules;
            
            if (!$schedulesCollection || $schedulesCollection->isEmpty()) {
                return false;
            }
            
            foreach ($schedulesCollection as $schedule) {
                if ($schedule->user_id !== $userId) {
                    continue;
                }
                
                // Skip if this doesn't span the day
                if (!$schedule->spansDay($day, $this->currentDate->year, $this->currentDate->month)) {
                    continue;
                }
                
                // Check if this event started on a previous day and spans over this day
                if ($schedule->start_date && $schedule->end_date) {
                    $currentMonth = $this->currentDate->month;
                    $currentYear = $this->currentDate->year;
                    
                    $eventStartDay = 1; // Default for events starting before current month
                    if ($schedule->start_date->year === $currentYear && $schedule->start_date->month === $currentMonth) {
                        $eventStartDay = $schedule->start_date->day;
                    }
                    
                    // If the event started before this day, then this day is covered
                    if ($eventStartDay < $day) {
                        return true;
                    }
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            logger('Error in isDayCoveredBySpanningEvent: ' . $e->getMessage());
            return false;
        }
    }

    // Schedule event creation methods
    public function createScheduleEvent()
    {
        // Check permissions using policy
        $this->authorize('create', Schedule::class);

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

    public function editScheduleEvent($scheduleId)
    {
        try {
            $schedule = Schedule::findOrFail($scheduleId);
            
            // Check permissions using policy
            $this->authorize('update', $schedule);

            // Populate form with schedule data
            $this->scheduleForm = [
                'user_id' => $schedule->user_id,
                'event_type_id' => $schedule->event_type_id,
                'start_date' => $schedule->start_date->format('Y-m-d'),
                'end_date' => $schedule->end_date->format('Y-m-d'),
                'remarks' => $schedule->remarks,
            ];

            $this->selectedScheduleId = $schedule->id;
            $this->scheduleEditMode = true;
            $this->showScheduleModal = true;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load schedule event: ' . $e->getMessage());
        }
    }

    public function deleteScheduleEvent($scheduleId)
    {
        try {
            $schedule = Schedule::findOrFail($scheduleId);
            
            // Check permissions using policy
            $this->authorize('delete', $schedule);

            $schedule->delete();
            session()->flash('message', 'Schedule event deleted successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete schedule event: ' . $e->getMessage());
        }
    }

    public function saveScheduleEvent()
    {
        // Check permissions using policy
        if ($this->scheduleEditMode) {
            $schedule = Schedule::findOrFail($this->selectedScheduleId);
            $this->authorize('update', $schedule);
        } else {
            $this->authorize('create', Schedule::class);
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
