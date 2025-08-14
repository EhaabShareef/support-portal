<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Component;

/**
 * Parent shell for contract-first hardware creation.
 * Maps existing hardware flow files:
 * - app/Livewire/ManageHardware.php
 * - app/Livewire/OrganizationHardwareForm.php
 * - resources/views/livewire/manage-hardware.blade.php
 * - resources/views/livewire/organization-hardware-form.blade.php
 * - resources/views/livewire/partials/organization/hardware.blade.php
 * - resources/views/livewire/partials/organization/hardware-tab.blade.php
 * - routes/web.php (hardware.manage route)
 * - app/Http/Controllers/OrganizationHardwareController.php
 * - no dedicated policy found for OrganizationHardware
 */
class OrganizationHardwareWizard extends Component
{
    public Organization $organization;
    public string $step = 'contract';
    public ?int $contractId = null;
    public ?int $hardwareId = null;
    public bool $serialRequired = false;
    public int $quantity = 0;

    protected $listeners = [
        'contractSelected' => 'onContractSelected',
        'hardwareCreated' => 'onHardwareCreated',
        'serialsComplete' => 'onSerialsComplete',
    ];

    public function mount(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function onContractSelected(int $contractId): void
    {
        $this->contractId = $contractId;
        $this->step = 'hardware';
    }

    public function onHardwareCreated(int $hardwareId, bool $serialRequired, int $quantity): void
    {
        $this->hardwareId = $hardwareId;
        $this->serialRequired = $serialRequired;
        $this->quantity = $quantity;
        $this->step = $serialRequired ? 'serials' : 'done';
    }

    public function onSerialsComplete(): void
    {
        $this->step = 'done';
    }

    public function render()
    {
        return view('livewire.organization-hardware-wizard');
    }
}
