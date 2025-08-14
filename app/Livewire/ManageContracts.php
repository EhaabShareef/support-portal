<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\Department;
use Livewire\WithPagination;

class ManageContracts extends Component
{
    use WithPagination;

    public Organization $organization;

    public $showForm = false;
    public $deleteId = null;
    public $editingContract = null;

    public array $form = [
        'contract_number' => '',
        'department_id' => '',
        'type' => 'support',
        'status' => 'active',
        'includes_hardware' => false,
        'is_oracle' => false,
        'csi_number' => '',
        'start_date' => '',
        'end_date' => '',
        'renewal_months' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.contract_number' => 'required|string|max:255',
        'form.department_id' => 'required|exists:departments,id',
        'form.type' => 'required|in:support,hardware,software,consulting,maintenance',
        'form.status' => 'required|in:draft,active,expired,terminated,renewed',
        'form.includes_hardware' => 'boolean',
        'form.is_oracle' => 'boolean',
        'form.csi_number' => 'required_if:form.is_oracle,true|nullable|string|max:255',
        'form.start_date' => 'required|date',
        'form.end_date' => 'nullable|date|after_or_equal:form.start_date',
        'form.renewal_months' => 'nullable|integer|min:1|max:120',
        'form.notes' => 'nullable|string',
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function render()
    {
        $contracts = OrganizationContract::where('organization_id', $this->organization->id)
            ->with('department')
            ->orderByDesc('start_date')
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('livewire.manage-contracts', [
            'contracts' => $contracts,
            'departments' => $departments
        ]);
    }

    public function create()
    {
        $this->reset(['form', 'editingContract']);
        
        $this->form = [
            'contract_number' => 'CON-' . strtoupper(uniqid()),
            'department_id' => '',
            'type' => 'support',
            'status' => 'active',
            'includes_hardware' => false,
            'is_oracle' => false,
            'csi_number' => null,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => null,
            'renewal_months' => null,
            'notes' => null,
        ];
        
        $this->showForm = true;
    }

    public function edit($id)
    {
        $this->editingContract = OrganizationContract::findOrFail($id);

        $this->form = [
            'contract_number' => $this->editingContract->contract_number,
            'department_id' => $this->editingContract->department_id,
            'type' => $this->editingContract->type,
            'status' => $this->editingContract->status,
            'includes_hardware' => $this->editingContract->includes_hardware,
            'is_oracle' => $this->editingContract->is_oracle,
            'csi_number' => $this->editingContract->csi_number,
            'start_date' => $this->editingContract->start_date?->format('Y-m-d') ?? '',
            'end_date' => $this->editingContract->end_date?->format('Y-m-d') ?? '',
            'renewal_months' => $this->editingContract->renewal_months,
            'notes' => $this->editingContract->notes,
        ];

        $this->showForm = true;
    }

    public function save()
    {
        // Add unique rule for contract number, excluding current contract if editing
        $contractNumberRule = 'required|string|max:255|unique:organization_contracts,contract_number';
        if ($this->editingContract) {
            $contractNumberRule .= ',' . $this->editingContract->id;
        }
        
        $this->rules['form.contract_number'] = $contractNumberRule;
        
        $this->validate();

        $data = $this->form;
        $data['organization_id'] = $this->organization->id;

        // Convert empty strings to null for nullable numeric fields
        $nullableFields = ['renewal_months'];
        foreach ($nullableFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty strings to null for nullable text fields
        $nullableTextFields = ['end_date', 'csi_number', 'notes'];
        foreach ($nullableTextFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if ($this->editingContract) {
            $this->editingContract->update($data);
            $message = 'Contract updated successfully.';
        } else {
            OrganizationContract::create($data);
            $message = 'Contract created successfully.';
        }

        $this->reset(['showForm', 'form', 'editingContract']);
        session()->flash('message', $message);
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }

    public function delete()
    {
        OrganizationContract::findOrFail($this->deleteId)->delete();
        $this->reset('deleteId');
        session()->flash('message', 'Contract deleted successfully.');
    }

    public function cancel()
    {
        $this->reset(['showForm', 'form', 'editingContract']);
    }
}
