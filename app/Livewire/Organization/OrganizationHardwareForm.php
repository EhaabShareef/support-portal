<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Services\HardwareValidationService;
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
        'purchase_date' => '',
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
        'form.purchase_date' => 'nullable|date',
        'form.location' => 'nullable|string|max:255',
        'form.remarks' => 'nullable|string',
        'form.last_maintenance' => 'nullable|date',
        'form.next_maintenance' => 'nullable|date',
    ];

    protected $messages = [
        'form.hardware_type.required' => 'Hardware type is required',
        'form.contract_id.exists' => 'Selected contract does not exist',
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
            'purchase_date' => null,
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
            'purchase_date' => $this->hardware->purchase_date?->format('Y-m-d') ?? '',
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

        // Validate hardware contract requirements
        $validation = HardwareValidationService::validateHardwareContract($this->organization, $this->form['contract_id']);
        if (!$validation['valid']) {
            $this->addError('form.contract_id', $validation['error']);
            return;
        }
        
        $this->validate();

        $data = $this->form;
        $data['organization_id'] = $this->organization->id;

        // Convert empty strings to null for date fields
        $dateFields = ['purchase_date', 'last_maintenance', 'next_maintenance'];
        foreach ($dateFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty strings to null for nullable numeric fields
        $nullableFields = ['contract_id'];
        foreach ($nullableFields as $field) {
            if (empty($data[$field]) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // Convert empty strings to null for nullable text fields
        $nullableTextFields = ['asset_tag', 'brand', 'model', 'serial_number', 'location', 'remarks'];
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