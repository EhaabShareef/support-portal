<?php

namespace App\Livewire\Settings\Users;

use App\Models\Department;
use App\Models\DepartmentGroup;
use Livewire\Component;
use Livewire\WithPagination;

class Departments extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterGroup = '';
    
    // Form properties
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public ?int $editingId = null;
    
    // Create form fields
    public string $name = '';
    public string $description = '';
    public ?int $department_group_id = null;
    public int $sort_order = 0;
    public bool $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'department_group_id' => 'nullable|exists:department_groups,id',
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
    ];

    public function updating($field): void
    {
        if (in_array($field, ['search', 'filterGroup'])) {
            $this->resetPage();
        }
    }

    public function create()
    {
        $this->validate();
        
        Department::create([
            'name' => $this->name,
            'description' => $this->description,
            'department_group_id' => $this->department_group_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);
        
        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', 'Department created successfully.');
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $this->editingId = $id;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->department_group_id = $department->department_group_id;
        $this->sort_order = $department->sort_order;
        $this->is_active = $department->is_active;
        $this->showEditForm = true;
    }

    public function update()
    {
        $this->validate();
        
        $department = Department::findOrFail($this->editingId);
        $department->update([
            'name' => $this->name,
            'description' => $this->description,
            'department_group_id' => $this->department_group_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);
        
        $this->resetForm();
        $this->showEditForm = false;
        session()->flash('message', 'Department updated successfully.');
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        session()->flash('message', 'Department deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $department = Department::findOrFail($id);
        $department->update(['is_active' => !$department->is_active]);
        session()->flash('message', 'Department status updated successfully.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->department_group_id = null;
        $this->sort_order = 0;
        $this->is_active = true;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Department::query()
            ->with('departmentGroup')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterGroup) {
            $query->where('department_group_id', $this->filterGroup);
        }

        return view('livewire.settings.users.departments', [
            'departments' => $query->paginate(10),
            'groups' => DepartmentGroup::orderBy('name')->get(),
        ]);
    }
}

