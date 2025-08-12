<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\ScheduleEventType;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ScheduleEventTypes extends Component
{
    public bool $showEventTypeModal = false;
    public bool $eventTypeEditMode = false;
    public ?int $selectedEventTypeId = null;
    public array $eventTypeForm = [
        'label' => '',
        'description' => '',
        'color' => '#3b82f6',
        'tailwind_classes' => 'bg-blue-500 text-white border-blue-600',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Delete confirmations
    public ?int $confirmingEventTypeDelete = null;

    #[Computed]
    public function scheduleEventTypes()
    {
        return ScheduleEventType::ordered()->get();
    }

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
        $this->eventTypeForm = $eventType->toArray();
        $this->eventTypeEditMode = true;
        $this->showEventTypeModal = true;
    }

    public function saveEventType()
    {
        $this->checkPermission($this->eventTypeEditMode ? 'schedule-event-types.update' : 'schedule-event-types.create');

        $rules = [
            'eventTypeForm.label' => 'required|string|max:255',
            'eventTypeForm.description' => 'nullable|string|max:1000',
            'eventTypeForm.color' => 'required|string|max:50',
            'eventTypeForm.tailwind_classes' => 'required|string|max:255',
            'eventTypeForm.is_active' => 'boolean',
            'eventTypeForm.sort_order' => 'integer|min:0',
        ];

        if (!$this->eventTypeEditMode) {
            $rules['eventTypeForm.label'] .= '|unique:schedule_event_types,label';
        } else {
            $rules['eventTypeForm.label'] .= '|unique:schedule_event_types,label,' . $this->selectedEventTypeId;
        }

        $validated = $this->validate($rules);

        try {
            if ($this->eventTypeEditMode) {
                ScheduleEventType::findOrFail($this->selectedEventTypeId)->update($validated['eventTypeForm']);
                $message = 'Schedule event type updated successfully.';
            } else {
                $validated['eventTypeForm']['created_by'] = auth()->id();
                ScheduleEventType::create($validated['eventTypeForm']);
                $message = 'Schedule event type created successfully.';
            }

            $this->closeEventTypeModal();
            $this->dispatch('flash', $message, 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save event type: ' . $e->getMessage(), 'error');
        }
    }

    public function confirmDeleteEventType($id)
    {
        $this->checkPermission('schedule-event-types.delete');
        $eventType = ScheduleEventType::withCount('schedules')->findOrFail($id);
        if ($eventType->schedules_count > 0) {
            $this->dispatch('flash', 'Cannot delete event type with associated schedules.', 'error');
            return;
        }
        $this->confirmingEventTypeDelete = $id;
    }

    public function deleteEventType()
    {
        $this->checkPermission('schedule-event-types.delete');
        
        try {
            ScheduleEventType::findOrFail($this->confirmingEventTypeDelete)->delete();
            $this->confirmingEventTypeDelete = null;
            $this->dispatch('flash', 'Schedule event type deleted successfully.', 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to delete event type: ' . $e->getMessage(), 'error');
        }
    }

    public function closeEventTypeModal()
    {
        $this->showEventTypeModal = false;
        $this->resetEventTypeForm();
    }

    public function cancelDelete()
    {
        $this->confirmingEventTypeDelete = null;
    }

    private function resetEventTypeForm()
    {
        $this->eventTypeForm = [
            'label' => '',
            'description' => '',
            'color' => '#3b82f6',
            'tailwind_classes' => 'bg-blue-500 text-white border-blue-600',
            'is_active' => true,
            'sort_order' => 0,
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
        return view('livewire.admin.settings.tabs.schedule-event-types');
    }
}