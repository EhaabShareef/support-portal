<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Models\ScheduleEventType;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsSchedule extends Component
{
    // Weekend Days Configuration
    public array $weekendDays = [];
    public bool $weekendDaysChanged = false;

    // Schedule Event Types Management
    public bool $showEventTypeModal = false;
    public bool $eventTypeEditMode = false;
    public ?int $selectedEventTypeId = null;
    public array $eventTypeForm = [
        'label' => '',
        'description' => '',
        'color' => '#3b82f6',
        'is_active' => true,
        'sort_order' => 0,
    ];

    public ?int $confirmingEventTypeDelete = null;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Load weekend days setting
            $weekendDaysData = $repository->get('weekend_days', ['saturday', 'sunday']);
            $this->weekendDays = is_array($weekendDaysData) ? $weekendDaysData : ['saturday', 'sunday'];
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to load schedule settings: ' . $e->getMessage());
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    #[Computed]
    public function scheduleEventTypes()
    {
        return ScheduleEventType::orderBy('sort_order')->orderBy('label')->get();
    }

    public function getDaysOfWeekProperty()
    {
        return [
            'sunday' => 'Sunday',
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
        ];
    }

    // Weekend Days Methods
    public function updatedWeekendDays()
    {
        $this->weekendDaysChanged = true;
    }

    public function saveWeekendDays()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'weekendDays' => 'array',
            'weekendDays.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        try {
            $repository = app(SettingsRepositoryInterface::class);
            $repository->set('weekend_days', $this->weekendDays, 'array');
            
            $this->weekendDaysChanged = false;
            $this->dispatch('saved', 'Weekend days updated successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save weekend days: ' . $e->getMessage());
        }
    }

    public function resetWeekendDays()
    {
        $this->checkPermission('settings.update');
        
        try {
            $repository = app(SettingsRepositoryInterface::class);
            $repository->reset('weekend_days');
            
            $this->loadData();
            $this->weekendDaysChanged = false;
            $this->dispatch('reset', 'Weekend days reset to defaults.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset weekend days: ' . $e->getMessage());
        }
    }

    // Schedule Event Type Methods
    public function createEventType()
    {
        $this->checkPermission('schedule-event-types.create');
        $this->resetEventTypeForm();
        $this->eventTypeEditMode = false;
        $this->showEventTypeModal = true;
    }

    public function editEventType($id)
    {
        $this->checkPermission('schedule-event-types.update');
        $eventType = ScheduleEventType::findOrFail($id);
        $this->selectedEventTypeId = $id;
        $this->eventTypeForm = [
            'label' => $eventType->label,
            'description' => $eventType->description,
            'color' => $eventType->color,
            'is_active' => $eventType->is_active,
            'sort_order' => $eventType->sort_order,
        ];
        $this->eventTypeEditMode = true;
        $this->showEventTypeModal = true;
    }

    public function saveEventType()
    {
        $this->checkPermission($this->eventTypeEditMode ? 'schedule-event-types.update' : 'schedule-event-types.create');

        $this->validate([
            'eventTypeForm.label' => 'required|string|max:255',
            'eventTypeForm.description' => 'nullable|string',
            'eventTypeForm.color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'eventTypeForm.is_active' => 'boolean',
            'eventTypeForm.sort_order' => 'integer|min:0',
        ]);

        try {
            if ($this->eventTypeEditMode) {
                $eventType = ScheduleEventType::findOrFail($this->selectedEventTypeId);
                $eventType->update([
                    'label' => $this->eventTypeForm['label'],
                    'description' => $this->eventTypeForm['description'],
                    'color' => $this->eventTypeForm['color'],
                    'is_active' => $this->eventTypeForm['is_active'],
                    'sort_order' => $this->eventTypeForm['sort_order'],
                ]);
                $message = 'Event type updated successfully.';
            } else {
                ScheduleEventType::create([
                    'label' => $this->eventTypeForm['label'],
                    'description' => $this->eventTypeForm['description'],
                    'color' => $this->eventTypeForm['color'],
                    'is_active' => $this->eventTypeForm['is_active'],
                    'sort_order' => $this->eventTypeForm['sort_order'],
                    'created_by' => auth()->id(),
                ]);
                $message = 'Event type created successfully.';
            }

            $this->closeEventTypeModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save event type: ' . $e->getMessage());
        }
    }

    public function confirmDeleteEventType($id)
    {
        $this->checkPermission('schedule-event-types.delete');
        
        // TODO: Check if event type is in use by any schedules
        // $eventType = ScheduleEventType::withCount('schedules')->findOrFail($id);
        // if ($eventType->schedules_count > 0) {
        //     $this->dispatch('error', 'Cannot delete event type that is in use by schedules.');
        //     return;
        // }
        
        $this->confirmingEventTypeDelete = $id;
    }

    public function deleteEventType()
    {
        $this->checkPermission('schedule-event-types.delete');
        
        try {
            ScheduleEventType::findOrFail($this->confirmingEventTypeDelete)->delete();
            $this->confirmingEventTypeDelete = null;
            $this->dispatch('saved', 'Event type deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete event type: ' . $e->getMessage());
        }
    }

    public function closeEventTypeModal()
    {
        $this->showEventTypeModal = false;
        $this->resetEventTypeForm();
    }

    public function cancelEventTypeDelete()
    {
        $this->confirmingEventTypeDelete = null;
    }

    public function toggleEventTypeStatus($id)
    {
        $this->checkPermission('schedule-event-types.update');
        
        try {
            $eventType = ScheduleEventType::findOrFail($id);
            $eventType->update(['is_active' => !$eventType->is_active]);
            
            $status = $eventType->is_active ? 'enabled' : 'disabled';
            $this->dispatch('saved', "Event type {$status} successfully.");
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update event type status: ' . $e->getMessage());
        }
    }

    // Helper Methods
    private function resetEventTypeForm()
    {
        $this->eventTypeForm = [
            'label' => '',
            'description' => '',
            'color' => '#3b82f6',
            'is_active' => true,
            'sort_order' => $this->scheduleEventTypes->count() + 1,
        ];
        $this->selectedEventTypeId = null;
        $this->resetErrorBag('eventTypeForm');
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.schedule');
    }
}