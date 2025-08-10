<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\DepartmentGroup;
use App\Models\Setting;
use App\Models\ScheduleEventType;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageSettings extends Component
{
    public string $activeTab = 'application';

    // Department Groups
    public bool $showDeptGroupModal = false;
    public bool $deptGroupEditMode = false;
    public ?int $selectedDeptGroupId = null;
    public array $deptGroupForm = [
        'name' => '',
        'description' => '',
        'color' => '#3B82F6',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Departments
    public bool $showDeptModal = false;
    public bool $deptEditMode = false;
    public ?int $selectedDeptId = null;
    public array $deptForm = [
        'name' => '',
        'description' => '',
        'department_group_id' => '',
        'email' => '',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Application Settings
    public bool $showSettingModal = false;
    public bool $settingEditMode = false;
    public ?int $selectedSettingId = null;
    public array $settingForm = [
        'key' => '',
        'value' => '',
        'type' => 'string',
        'group' => 'general',
        'label' => '',
        'description' => '',
        'is_public' => false,
        'is_encrypted' => false,
        'validation_rules' => [],
    ];

    public string $validationRulesText = '';

    // Schedule Event Types
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
    public ?int $confirmingDeptGroupDelete = null;
    public ?int $confirmingDeptDelete = null;
    public ?int $confirmingSettingDelete = null;
    public ?int $confirmingEventTypeDelete = null;

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to manage settings.');
        }
    }

    #[Computed]
    public function departmentGroups()
    {
        return DepartmentGroup::withCount('departments')->ordered()->get();
    }

    #[Computed]
    public function departments()
    {
        return Department::with('departmentGroup')->withCount(['users', 'tickets'])->ordered()->get();
    }

    #[Computed]
    public function applicationSettings()
    {
        return Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
    }

    #[Computed]
    public function availableDeptGroups()
    {
        return DepartmentGroup::active()->ordered()->get();
    }

    #[Computed]
    public function scheduleEventTypes()
    {
        return ScheduleEventType::ordered()->get();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Department Groups CRUD
    public function createDeptGroup()
    {
        $this->resetDeptGroupForm();
        $this->deptGroupEditMode = false;
        $this->showDeptGroupModal = true;
    }

    public function editDeptGroup($id)
    {
        $group = DepartmentGroup::findOrFail($id);
        $this->selectedDeptGroupId = $id;
        $this->deptGroupForm = $group->toArray();
        $this->deptGroupEditMode = true;
        $this->showDeptGroupModal = true;
    }

    public function saveDeptGroup()
    {
        $validated = $this->validate([
            'deptGroupForm.name' => 'required|string|max:255',
            'deptGroupForm.description' => 'nullable|string',
            'deptGroupForm.color' => 'nullable|string|max:7',
            'deptGroupForm.is_active' => 'boolean',
            'deptGroupForm.sort_order' => 'integer|min:0',
        ]);

        if ($this->deptGroupEditMode) {
            DepartmentGroup::findOrFail($this->selectedDeptGroupId)->update($validated['deptGroupForm']);
            session()->flash('message', 'Department group updated successfully.');
        } else {
            DepartmentGroup::create($validated['deptGroupForm']);
            session()->flash('message', 'Department group created successfully.');
        }

        $this->closeDeptGroupModal();
    }

    public function confirmDeleteDeptGroup($id)
    {
        $group = DepartmentGroup::withCount('departments')->findOrFail($id);
        if ($group->departments_count > 0) {
            session()->flash('error', 'Cannot delete department group with associated departments.');
            return;
        }
        $this->confirmingDeptGroupDelete = $id;
    }

    public function deleteDeptGroup()
    {
        DepartmentGroup::findOrFail($this->confirmingDeptGroupDelete)->delete();
        $this->confirmingDeptGroupDelete = null;
        session()->flash('message', 'Department group deleted successfully.');
    }

    public function closeDeptGroupModal()
    {
        $this->showDeptGroupModal = false;
        $this->resetDeptGroupForm();
    }

    private function resetDeptGroupForm()
    {
        $this->deptGroupForm = [
            'name' => '',
            'description' => '',
            'color' => '#3B82F6',
            'is_active' => true,
            'sort_order' => 0,
        ];
        $this->selectedDeptGroupId = null;
        $this->resetErrorBag('deptGroupForm');
    }

    // Departments CRUD
    public function createDept()
    {
        $this->resetDeptForm();
        $this->deptEditMode = false;
        $this->showDeptModal = true;
    }

    public function editDept($id)
    {
        $dept = Department::findOrFail($id);
        $this->selectedDeptId = $id;
        $this->deptForm = $dept->toArray();
        $this->deptEditMode = true;
        $this->showDeptModal = true;
    }

    public function saveDept()
    {
        $validated = $this->validate([
            'deptForm.name' => 'required|string|max:255',
            'deptForm.description' => 'nullable|string',
            'deptForm.department_group_id' => 'nullable|exists:department_groups,id',
            'deptForm.email' => 'nullable|email|max:255',
            'deptForm.is_active' => 'boolean',
            'deptForm.sort_order' => 'integer|min:0',
        ]);

        if ($this->deptEditMode) {
            Department::findOrFail($this->selectedDeptId)->update($validated['deptForm']);
            session()->flash('message', 'Department updated successfully.');
        } else {
            Department::create($validated['deptForm']);
            session()->flash('message', 'Department created successfully.');
        }

        $this->closeDeptModal();
    }

    public function confirmDeleteDept($id)
    {
        $dept = Department::withCount(['users', 'tickets'])->findOrFail($id);
        if ($dept->users_count > 0 || $dept->tickets_count > 0) {
            session()->flash('error', 'Cannot delete department with associated users or tickets.');
            return;
        }
        $this->confirmingDeptDelete = $id;
    }

    public function deleteDept()
    {
        Department::findOrFail($this->confirmingDeptDelete)->delete();
        $this->confirmingDeptDelete = null;
        session()->flash('message', 'Department deleted successfully.');
    }

    public function closeDeptModal()
    {
        $this->showDeptModal = false;
        $this->resetDeptForm();
    }

    private function resetDeptForm()
    {
        $this->deptForm = [
            'name' => '',
            'description' => '',
            'department_group_id' => '',
            'email' => '',
            'is_active' => true,
            'sort_order' => 0,
        ];
        $this->selectedDeptId = null;
        $this->resetErrorBag('deptForm');
    }

    // Application Settings CRUD
    public function createSetting()
    {
        $this->resetSettingForm();
        $this->settingEditMode = false;
        $this->showSettingModal = true;
    }

    public function editSetting($id)
    {
        $setting = Setting::findOrFail($id);
        $this->selectedSettingId = $id;
        $this->settingForm = $setting->toArray();
        $this->validationRulesText = $setting->getValidationRulesString();
        $this->settingEditMode = true;
        $this->showSettingModal = true;
    }

    public function saveSetting()
    {
        $rules = [
            'settingForm.key' => 'required|string|max:255',
            'settingForm.value' => 'nullable',
            'settingForm.type' => 'required|in:string,integer,float,boolean,json,array',
            'settingForm.group' => 'required|string|max:255',
            'settingForm.label' => 'required|string|max:255',
            'settingForm.description' => 'nullable|string',
            'settingForm.is_public' => 'boolean',
            'settingForm.is_encrypted' => 'boolean',
        ];

        if (!$this->settingEditMode) {
            $rules['settingForm.key'] .= '|unique:settings,key';
        } else {
            $rules['settingForm.key'] .= '|unique:settings,key,' . $this->selectedSettingId;
        }

        $validated = $this->validate($rules);

        // Parse validation rules
        if ($this->validationRulesText) {
            $validated['settingForm']['validation_rules'] = explode('|', $this->validationRulesText);
        } else {
            $validated['settingForm']['validation_rules'] = null;
        }

        if ($this->settingEditMode) {
            Setting::findOrFail($this->selectedSettingId)->update($validated['settingForm']);
            session()->flash('message', 'Setting updated successfully.');
        } else {
            Setting::create($validated['settingForm']);
            session()->flash('message', 'Setting created successfully.');
        }

        $this->closeSettingModal();
    }

    public function confirmDeleteSetting($id)
    {
        $this->confirmingSettingDelete = $id;
    }

    public function deleteSetting()
    {
        Setting::findOrFail($this->confirmingSettingDelete)->delete();
        $this->confirmingSettingDelete = null;
        session()->flash('message', 'Setting deleted successfully.');
    }

    public function closeSettingModal()
    {
        $this->showSettingModal = false;
        $this->resetSettingForm();
    }

    private function resetSettingForm()
    {
        $this->settingForm = [
            'key' => '',
            'value' => '',
            'type' => 'string',
            'group' => 'general',
            'label' => '',
            'description' => '',
            'is_public' => false,
            'is_encrypted' => false,
            'validation_rules' => [],
        ];
        $this->validationRulesText = '';
        $this->selectedSettingId = null;
        $this->resetErrorBag('settingForm');
    }

    // Schedule Event Types CRUD
    public function createEventType()
    {
        $this->resetEventTypeForm();
        $this->eventTypeEditMode = false;
        $this->showEventTypeModal = true;
    }

    public function editEventType($id)
    {
        $eventType = ScheduleEventType::findOrFail($id);
        $this->selectedEventTypeId = $id;
        $this->eventTypeForm = $eventType->toArray();
        $this->eventTypeEditMode = true;
        $this->showEventTypeModal = true;
    }

    public function saveEventType()
    {
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

        if ($this->eventTypeEditMode) {
            ScheduleEventType::findOrFail($this->selectedEventTypeId)->update($validated['eventTypeForm']);
            session()->flash('message', 'Schedule event type updated successfully.');
        } else {
            $validated['eventTypeForm']['created_by'] = auth()->id();
            ScheduleEventType::create($validated['eventTypeForm']);
            session()->flash('message', 'Schedule event type created successfully.');
        }

        $this->closeEventTypeModal();
    }

    public function confirmDeleteEventType($id)
    {
        $eventType = ScheduleEventType::withCount('schedules')->findOrFail($id);
        if ($eventType->schedules_count > 0) {
            session()->flash('error', 'Cannot delete event type with associated schedules.');
            return;
        }
        $this->confirmingEventTypeDelete = $id;
    }

    public function deleteEventType()
    {
        ScheduleEventType::findOrFail($this->confirmingEventTypeDelete)->delete();
        $this->confirmingEventTypeDelete = null;
        session()->flash('message', 'Schedule event type deleted successfully.');
    }

    public function closeEventTypeModal()
    {
        $this->showEventTypeModal = false;
        $this->resetEventTypeForm();
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

    public function cancelDelete()
    {
        $this->confirmingDeptGroupDelete = null;
        $this->confirmingDeptDelete = null;
        $this->confirmingSettingDelete = null;
        $this->confirmingEventTypeDelete = null;
    }

    public function render()
    {
        return view('livewire.admin.manage-settings');
    }
}