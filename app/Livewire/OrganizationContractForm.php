<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\ContractType;
use App\Models\ContractStatus;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrganizationContractForm extends Component
{
    public Organization $organization;
    public ?OrganizationContract $contract = null;
    public bool $isEditing = false;

    public array $form = [
        'contract_number' => '',
        'department_id' => '',
        'type' => '',
        'status' => '',
        'includes_hardware' => false,
        'is_oracle' => false,
        'csi_number' => '',
        'start_date' => '',
        'end_date' => '',
        'renewal_months' => '',
        'csi_remarks' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.contract_number' => 'required|string|max:255',
        'form.department_id' => 'required|exists:departments,id',
        'form.type' => 'required|string|exists:contract_types,slug',
        'form.status' => 'required|string|exists:contract_statuses,slug',
        'form.includes_hardware' => 'boolean',
        'form.is_oracle' => 'boolean',
        'form.csi_number' => 'required_if:form.is_oracle,true|nullable|string|max:255',
        'form.start_date' => 'required|date',
        'form.end_date' => 'nullable|date|after_or_equal:form.start_date',
        'form.renewal_months' => 'nullable|integer|min:1|max:120',
        'form.csi_remarks' => 'nullable|string',
        'form.notes' => 'nullable|string',
    ];

    protected $messages = [
        'form.contract_number.required' => 'Contract number is required',
        'form.contract_number.unique' => 'This contract number already exists',
        'form.department_id.required' => 'Department is required',
        'form.department_id.exists' => 'Selected department does not exist',
        'form.start_date.required' => 'Start date is required',
        'form.end_date.after_or_equal' => 'End date must be after or equal to start date',
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        $this->resetToNew();
    }
    
    private function resetToNew()
    {
        $this->isEditing = false;
        $this->contract = null;
        
        // Get default values from first available options
        $defaultType = ContractType::active()->ordered()->first()?->slug ?? '';
        $defaultStatus = ContractStatus::active()->ordered()->first()?->slug ?? '';
        
        $this->form = [
            'contract_number' => 'CON-' . strtoupper(uniqid()),
            'department_id' => '',
            'type' => $defaultType,
            'status' => $defaultStatus,
            'includes_hardware' => false,
            'is_oracle' => false,
            'csi_number' => null,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => null,
            'renewal_months' => null,
            'csi_remarks' => null,
            'notes' => null,
        ];
    }

    #[On('newContract')]
    public function newContract()
    {
        $this->resetToNew();
    }

    #[On('editContract')]
    public function editContract($contractId)
    {
        $this->contract = OrganizationContract::findOrFail($contractId);
        $this->isEditing = true;
        $this->loadContractData();
    }

    private function loadContractData()
    {
        if (!$this->contract) return;

        $this->form = [
            'contract_number' => $this->contract->contract_number,
            'department_id' => $this->contract->department_id,
            'type' => $this->contract->type,
            'status' => $this->contract->status,
            'includes_hardware' => $this->contract->includes_hardware,
            'is_oracle' => $this->contract->is_oracle,
            'csi_number' => $this->contract->csi_number,
            'start_date' => $this->contract->start_date?->format('Y-m-d') ?? '',
            'end_date' => $this->contract->end_date?->format('Y-m-d') ?? '',
            'renewal_months' => $this->contract->renewal_months,
            'csi_remarks' => $this->contract->csi_remarks,
            'notes' => $this->contract->notes,
        ];
    }

    public function save()
    {
        // Add unique rule for contract number, excluding current contract if editing
        $contractNumberRule = 'required|string|max:255|unique:organization_contracts,contract_number';
        if ($this->isEditing && $this->contract) {
            $contractNumberRule .= ',' . $this->contract->id;
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
        $nullableTextFields = ['end_date', 'csi_remarks', 'csi_number', 'notes'];
        foreach ($nullableTextFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if ($this->isEditing && $this->contract) {
            $this->contract->update($data);
            $message = 'Contract updated successfully.';
        } else {
            OrganizationContract::create($data);
            $message = 'Contract created successfully.';
        }

        $this->dispatch('contractSaved');
        $this->dispatch('refreshOrganization');
        session()->flash('message', $message);
        
        // Reset form and dispatch close modal event
        $this->reset(['form', 'isEditing', 'contract']);
    }

    public function cancel()
    {
        $this->reset(['form', 'isEditing', 'contract']);
        $this->dispatch('contractCancelled');
    }

    #[Computed]
    public function contractTypes()
    {
        return ContractType::active()->ordered()->get();
    }

    #[Computed]
    public function contractStatuses()
    {
        return ContractStatus::active()->ordered()->get();
    }

    #[Computed]
    public function contractTypeOptions()
    {
        return $this->contractTypes->pluck('name', 'slug')->toArray();
    }

    #[Computed]
    public function contractStatusOptions()
    {
        return $this->contractStatuses->pluck('name', 'slug')->toArray();
    }

    public function render()
    {
        $departments = Department::orderBy('name')->get();
        
        return view('livewire.organization-contract-form', [
            'departments' => $departments,
        ]);
    }
}