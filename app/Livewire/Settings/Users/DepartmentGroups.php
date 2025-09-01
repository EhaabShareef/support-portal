<?php

namespace App\Livewire\Settings\Users;

use App\Models\DepartmentGroup;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentGroups extends Component
{
    use WithPagination;

    public string $search = '';
    
    // Form properties
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public ?int $editingId = null;
    
    // Create form fields
    public string $name = '';
    public string $description = '';
    public string $color = '#3B82F6';
    public int $sort_order = 0;
    public bool $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'color' => 'required|string|max:7',
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->validate();
        
        DepartmentGroup::create([
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);
        
        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', 'Department group created successfully.');
    }

    public function edit($id)
    {
        $group = DepartmentGroup::findOrFail($id);
        $this->editingId = $id;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->color = $group->color;
        $this->sort_order = $group->sort_order;
        $this->is_active = $group->is_active;
        $this->showEditForm = true;
    }

    public function update()
    {
        $this->validate();
        
        $group = DepartmentGroup::findOrFail($this->editingId);
        $group->update([
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);
        
        $this->resetForm();
        $this->showEditForm = false;
        session()->flash('message', 'Department group updated successfully.');
    }

    public function delete($id)
    {
        $group = DepartmentGroup::findOrFail($id);
        $group->delete();
        session()->flash('message', 'Department group deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $group = DepartmentGroup::findOrFail($id);
        $group->update(['is_active' => !$group->is_active]);
        session()->flash('message', 'Department group status updated successfully.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#3B82F6';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = DepartmentGroup::query()
            ->withCount(['departments', 'users'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.settings.users.department-groups', [
            'groups' => $query->paginate(10),
        ]);
    }
}

