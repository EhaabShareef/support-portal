<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\OrganizationHardware;
use App\Models\OrganizationContract;
use App\Services\HardwareValidationService;
use Livewire\Component;
use Livewire\WithPagination;

class ManageHardware extends Component
{
    use WithPagination;

    public Organization $organization;
    public $showForm = false;
    public $deleteId = null;
    public $editingHardware = null;

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

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function render()
    {
        $hardwareList = OrganizationHardware::where('organization_id', $this->organization->id)
            ->with('contract')
            ->latest('purchase_date')
            ->paginate(10);

        $contracts = $this->organization->contracts()
            ->where('status', 'active')
            ->where('includes_hardware', true)
            ->orderBy('contract_number')
            ->get();

        return view('livewire.manage-hardware', [
            'hardwareList' => $hardwareList,
            'contracts' => $contracts
        ]);
    }

    public function create()
    {
        $this->reset(['form', 'editingHardware']);
        
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
        
        $this->showForm = true;
    }

    public function edit($id)
    {
        $this->editingHardware = OrganizationHardware::findOrFail($id);

        $this->form = [
            'asset_tag' => $this->editingHardware->asset_tag,
            'contract_id' => $this->editingHardware->contract_id,
            'hardware_type' => $this->editingHardware->hardware_type,
            'brand' => $this->editingHardware->brand,
            'model' => $this->editingHardware->model,
            'serial_number' => $this->editingHardware->serial_number,
            'specifications' => $this->editingHardware->specifications,
            'purchase_date' => $this->editingHardware->purchase_date?->format('Y-m-d') ?? '',
            'purchase_price' => $this->editingHardware->purchase_price,
            'warranty_start' => $this->editingHardware->warranty_start?->format('Y-m-d') ?? '',
            'warranty_expiration' => $this->editingHardware->warranty_expiration?->format('Y-m-d') ?? '',
            'status' => $this->editingHardware->status,
            'location' => $this->editingHardware->location,
            'remarks' => $this->editingHardware->remarks,
            'last_maintenance' => $this->editingHardware->last_maintenance?->format('Y-m-d') ?? '',
            'next_maintenance' => $this->editingHardware->next_maintenance?->format('Y-m-d') ?? '',
        ];

        $this->showForm = true;
    }

    public function save()
    {
        // Add unique rules for serial number and asset tag, excluding current hardware if editing
        $serialNumberRule = 'nullable|string|max:255|unique:organization_hardware,serial_number';
        $assetTagRule = 'nullable|string|max:255|unique:organization_hardware,asset_tag';
        
        if ($this->editingHardware) {
            $serialNumberRule .= ',' . $this->editingHardware->id;
            $assetTagRule .= ',' . $this->editingHardware->id;
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

        if ($this->editingHardware) {
            $this->editingHardware->update($data);
            $message = 'Hardware updated successfully.';
        } else {
            OrganizationHardware::create($data);
            $message = 'Hardware created successfully.';
        }

        $this->reset(['showForm', 'form', 'editingHardware']);
        session()->flash('message', $message);
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }

    public function delete()
    {
        OrganizationHardware::findOrFail($this->deleteId)->delete();
        $this->reset('deleteId');
        session()->flash('message', 'Hardware deleted successfully.');
    }

    public function cancel()
    {
        $this->reset(['showForm', 'form', 'editingHardware']);
    }
}
