<?php

namespace App\Livewire;

use App\Models\OrganizationContract;
use Livewire\Component;

class HardwareContractSelector extends Component
{
    public int $organizationId;
    public ?int $selected = null;

    public function mount(int $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function selectContract(): void
    {
        if ($this->selected) {
            $this->dispatch('contractSelected', $this->selected);
        }
    }

    public function render()
    {
        $contracts = OrganizationContract::where('organization_id', $this->organizationId)->get();
        return view('livewire.hardware-contract-selector', [
            'contracts' => $contracts,
        ]);
    }
}
