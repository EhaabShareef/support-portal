<?php

namespace App\Livewire;

use App\Models\HardwareType;
use App\Models\OrganizationHardware;
use Livewire\Component;

class HardwareFormSimple extends Component
{
    public int $organizationId;
    public int $contractId;

    public array $form = [
        'hardware_type_id' => '',
        'model' => '',
        'brand' => '',
        'quantity' => 1,
        'serial_required' => false,
        'remarks' => '',
    ];

    protected $rules = [
        'form.hardware_type_id' => 'required|exists:hardware_types,id',
        'form.model' => 'nullable|string|max:255',
        'form.brand' => 'nullable|string|max:255',
        'form.quantity' => 'required|integer|min:1',
        'form.serial_required' => 'boolean',
        'form.remarks' => 'nullable|string',
    ];

    public function mount(int $organizationId, int $contractId): void
    {
        $this->organizationId = $organizationId;
        $this->contractId = $contractId;
    }

    public function save(): void
    {
        $this->validate();
        $data = $this->form;
        $data['organization_id'] = $this->organizationId;
        $data['contract_id'] = $this->contractId;
        $hardware = OrganizationHardware::create($data);
        $this->dispatch('hardwareCreated', $hardware->id, $hardware->serial_required, $hardware->quantity);
    }

    public function render()
    {
        return view('livewire.hardware-form-simple', [
            'types' => HardwareType::all(),
        ]);
    }
}
