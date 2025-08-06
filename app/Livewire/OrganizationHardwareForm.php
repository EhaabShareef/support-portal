<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use Livewire\Attributes\On;
use Livewire\Component;

class OrganizationHardwareForm extends Component
{
    public Organization $organization;
    public ?OrganizationHardware $hardware = null;
    public bool $isEditing = false;

    public array $form = [
        'asset_tag' => '',
        'contract_id' => '',
        'hardware_type' => 'desktop',
        'brand' => '',
        'model' => '',
        'serial_number' => '',
        'specifications' => '',
        'purchase_date' => '',
        'purchase_price' => '',
        'warranty_start' => '',
        'warranty_expiration' => '',
        'status' => 'active',
        'location' => '',
        'remarks' => '',
        'last_maintenance' => '',
        'next_maintenance' => '',
    ];

    protected $rules = [
        'form.asset_tag' => 'nullable|string|max:255',
        'form.contract_id' => 'nullable|exists:organization_contracts,id',
        'form.hardware_type' => 'required|string|max:255',
        'form.brand' => 'nullable|string|max:255',
        'form.model' => 'nullable|string|max:255',
        'form.serial_number' => 'nullable|string|max:255',
        'form.specifications' => 'nullable|string',
        'form.purchase_date' => 'nullable|date',
        'form.purchase_price' => 'nullable|numeric|min:0',
        'form.warranty_start' => 'nullable|date',
        'form.warranty_expiration' => 'nullable|date|after_or_equal:form.warranty_start',
        'form.status' => 'required|in:active,maintenance,retired,disposed,lost',
        'form.location' => 'nullable|string|max:255',
        'form.remarks' => 'nullable|string',
        'form.last_maintenance' => 'nullable|date',
        'form.next_maintenance' => 'nullable|date',
    ];

    protected $messages = [
        'form.hardware_type.required' => 'Hardware type is required',
        'form.contract_id.exists' => 'Selected contract does not exist',
        'form.warranty_expiration.after_or_equal' => 'Warranty expiration must be after or equal to warranty start date',
        'form.serial_number.unique' => 'This serial number already exists',
        'form.asset_tag.unique' => 'This asset tag already exists',
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        $this->resetToNew();
    }
    
    private function resetToNew()
    {
        $this->isEditing = false;
        $this->hardware = null;
        $this->form = [
            'asset_tag' => 'HW-' . strtoupper(uniqid()),
            'contract_id' => null,
            'hardware_type' => 'desktop',
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'specifications' => null,
            'purchase_date' => null,
            'purchase_price' => null,
            'warranty_start' => null,
            'warranty_expiration' => null,
            'status' => 'active',
            'location' => null,
            'remarks' => null,
            'last_maintenance' => null,
            'next_maintenance' => null,
        ];
    }

    #[On('newHardware')]
    public function newHardware()
    {
        $this->resetToNew();
    }

    #[On('editHardware')]
    public function editHardware($hardwareId)
    {
        $this->hardware = OrganizationHardware::findOrFail($hardwareId);
        $this->isEditing = true;
        $this->loadHardwareData();
    }

    private function loadHardwareData()
    {
        if (!$this->hardware) return;

        $this->form = [
            'asset_tag' => $this->hardware->asset_tag,
            'contract_id' => $this->hardware->contract_id,
            'hardware_type' => $this->hardware->hardware_type,
            'brand' => $this->hardware->brand,
            'model' => $this->hardware->model,
            'serial_number' => $this->hardware->serial_number,
            'specifications' => $this->hardware->specifications,
            'purchase_date' => $this->hardware->purchase_date?->format('Y-m-d') ?? '',
            'purchase_price' => $this->hardware->purchase_price,
            'warranty_start' => $this->hardware->warranty_start?->format('Y-m-d') ?? '',
            'warranty_expiration' => $this->hardware->warranty_expiration?->format('Y-m-d') ?? '',
            'status' => $this->hardware->status,
            'location' => $this->hardware->location,
            'remarks' => $this->hardware->remarks,
            'last_maintenance' => $this->hardware->last_maintenance?->format('Y-m-d') ?? '',
            'next_maintenance' => $this->hardware->next_maintenance?->format('Y-m-d') ?? '',
        ];
    }

    public function save()
    {
        // Add unique rules for serial number and asset tag, excluding current hardware if editing
        $serialNumberRule = 'nullable|string|max:255|unique:organization_hardware,serial_number';
        $assetTagRule = 'nullable|string|max:255|unique:organization_hardware,asset_tag';
        
        if ($this->isEditing && $this->hardware) {
            $serialNumberRule .= ',' . $this->hardware->id;
            $assetTagRule .= ',' . $this->hardware->id;
        }
        
        $this->rules['form.serial_number'] = $serialNumberRule;
        $this->rules['form.asset_tag'] = $assetTagRule;

        // Check if hardware requires an active hardware contract
        if ($this->form['contract_id']) {
            $contract = OrganizationContract::find($this->form['contract_id']);
            if (!$contract || !$contract->includes_hardware || $contract->status !== 'active') {
                $this->addError('form.contract_id', 'Hardware can only be assigned to active contracts that include hardware.');
                return;
            }
        } else {
            // Check if organization has any active hardware contracts
            $hasHardwareContract = $this->organization->contracts()
                ->where('status', 'active')
                ->where('includes_hardware', true)
                ->exists();
                
            if (!$hasHardwareContract) {
                $this->addError('form.contract_id', 'This organization must have an active hardware contract to add hardware. Please create a hardware contract first.');
                return;
            }
        }
        
        $this->validate();

        $data = $this->form;
        $data['organization_id'] = $this->organization->id;

        // Convert empty strings to null for date fields
        $dateFields = ['purchase_date', 'warranty_start', 'warranty_expiration', 'last_maintenance', 'next_maintenance'];
        foreach ($dateFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty strings to null for nullable numeric fields
        $nullableFields = ['purchase_price', 'contract_id'];
        foreach ($nullableFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty strings to null for nullable text fields
        $nullableTextFields = ['asset_tag', 'brand', 'model', 'serial_number', 'specifications', 'location', 'remarks'];
        foreach ($nullableTextFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if ($this->isEditing && $this->hardware) {
            $this->hardware->update($data);
            $message = 'Hardware updated successfully.';
        } else {
            OrganizationHardware::create($data);
            $message = 'Hardware created successfully.';
        }

        $this->dispatch('hardwareSaved');
        $this->dispatch('refreshOrganization');
        session()->flash('message', $message);
        
        // Reset form and dispatch close modal event
        $this->reset(['form', 'isEditing', 'hardware']);
    }

    public function cancel()
    {
        $this->reset(['form', 'isEditing', 'hardware']);
        $this->dispatch('hardwareCancelled');
    }

    public function render()
    {
        $contracts = $this->organization->contracts()
            ->where('status', 'active')
            ->where('includes_hardware', true)
            ->orderBy('contract_number')
            ->get();
        
        return view('livewire.organization-hardware-form', [
            'contracts' => $contracts,
        ]);
    }
}