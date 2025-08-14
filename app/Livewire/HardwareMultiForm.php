<?php

namespace App\Livewire;

use App\Models\HardwareType;
use App\Models\OrganizationHardware;
use Livewire\Component;

class HardwareMultiForm extends Component
{
    public int $organizationId;
    public int $contractId;
    public array $hardwareItems = [];
    public array $currentForm = [
        'hardware_type_id' => '',
        'model' => '',
        'brand' => '',
        'quantity' => 1,
        'serial_required' => false,
        'remarks' => '',
    ];

    protected $rules = [
        'currentForm.hardware_type_id' => 'required|exists:hardware_types,id',
        'currentForm.model' => 'nullable|string|max:255',
        'currentForm.brand' => 'nullable|string|max:255',
        'currentForm.quantity' => 'required|integer|min:1',
        'currentForm.serial_required' => 'boolean',
        'currentForm.remarks' => 'nullable|string',
    ];

    public function mount(int $organizationId, int $contractId): void
    {
        $this->organizationId = $organizationId;
        $this->contractId = $contractId;
    }

    public function addHardware(): void
    {
        $this->validate();
        
        // Get hardware type name for display
        $hardwareType = HardwareType::find($this->currentForm['hardware_type_id']);
        
        $hardwareData = $this->currentForm;
        $hardwareData['organization_id'] = $this->organizationId;
        $hardwareData['contract_id'] = $this->contractId;
        $hardwareData['type_name'] = $hardwareType?->name ?? 'Unknown';
        
        // Create the hardware record
        $hardware = OrganizationHardware::create($hardwareData);
        
        // Add to our tracking array with the database ID
        $this->hardwareItems[] = [
            'id' => $hardware->id,
            'hardware_type_id' => $hardwareData['hardware_type_id'],
            'type_name' => $hardwareData['type_name'],
            'model' => $hardwareData['model'],
            'brand' => $hardwareData['brand'],
            'quantity' => $hardwareData['quantity'],
            'serial_required' => $hardwareData['serial_required'],
            'remarks' => $hardwareData['remarks'],
        ];
        
        // Reset form for next entry
        $this->resetCurrentForm();
        
        session()->flash('message', 'Hardware item added successfully!');
    }

    public function addAndContinue(): void
    {
        // If no items added yet, add current form first
        if (empty($this->hardwareItems) && $this->isCurrentFormValid()) {
            $this->addHardware();
        }
        
        if (!empty($this->hardwareItems)) {
            // Check if any hardware requires serials
            $requiresSerials = collect($this->hardwareItems)->where('serial_required', true);
            
            if ($requiresSerials->isNotEmpty()) {
                // Move to serials step with all hardware needing serials
                $this->dispatch('hardwareMultiCreated', $this->hardwareItems);
            } else {
                // Skip serials and go to completion
                $this->dispatch('hardwareCompleted');
            }
        }
    }

    public function removeHardware(int $index): void
    {
        if (isset($this->hardwareItems[$index])) {
            // Delete from database
            OrganizationHardware::find($this->hardwareItems[$index]['id'])?->delete();
            
            // Remove from array
            array_splice($this->hardwareItems, $index, 1);
            
            session()->flash('message', 'Hardware item removed successfully!');
        }
    }

    private function resetCurrentForm(): void
    {
        $this->currentForm = [
            'hardware_type_id' => '',
            'model' => '',
            'brand' => '',
            'quantity' => 1,
            'serial_required' => false,
            'remarks' => '',
        ];
        $this->resetErrorBag();
    }

    private function isCurrentFormValid(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function render()
    {
        return view('livewire.hardware-multi-form', [
            'types' => HardwareType::active()->ordered()->get(),
        ]);
    }
}