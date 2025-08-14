<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\OrganizationHardware;
use App\Models\OrganizationContract;
use App\Models\HardwareType;
use Livewire\Component;
use Livewire\WithPagination;

class ManageHardware extends Component
{
    use WithPagination;

    public Organization $organization;
    public $deleteId = null;
    
    // Contract Hardware Management Modal
    public $showContractModal = false;
    public $selectedContractId = null;
    public $contractHardware = [];
    public $showSerialModal = false;
    public $editingHardwareForSerial = null;
    
    // Filters
    public $filterContract = '';
    public $filterIsOracle = null;

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function render()
    {
        $query = OrganizationHardware::where('organization_id', $this->organization->id)
            ->with(['contract', 'type', 'serials']);
            
        // Apply filters
        if ($this->filterContract) {
            $query->where('contract_id', $this->filterContract);
        }
        
        if ($this->filterIsOracle !== null) {
            $query->whereHas('contract', function ($q) {
                $q->where('is_oracle', $this->filterIsOracle);
            });
        }
        
        $hardwareList = $query->latest('purchase_date')->get();

        // Group hardware by contract
        $groupedHardware = $hardwareList->groupBy(function ($hardware) {
            return $hardware->contract ? 
                $hardware->contract->contract_number . ' (' . ucfirst($hardware->contract->type) . ')' : 
                'No Contract Assigned';
        })->sortKeys();

        $contracts = $this->organization->contracts()
            ->where('status', 'active')
            ->where('includes_hardware', true)
            ->orderBy('contract_number')
            ->get();

        return view('livewire.manage-hardware', [
            'groupedHardware' => $groupedHardware,
            'contracts' => $contracts
        ]);
    }

    public function edit($id)
    {
        // Redirect to wizard for editing - we'll implement a simplified edit later if needed
        return redirect()->route('organizations.hardware.create', [
            'organization' => $this->organization->id,
            'edit' => $id
        ]);
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


    public function openContractModal($contractId)
    {
        $this->selectedContractId = $contractId;
        $this->loadContractHardware();
        $this->showContractModal = true;
    }

    public function closeContractModal()
    {
        $this->reset(['showContractModal', 'selectedContractId', 'contractHardware']);
    }

    public function loadContractHardware()
    {
        if ($this->selectedContractId) {
            $this->contractHardware = OrganizationHardware::where('organization_id', $this->organization->id)
                ->where('contract_id', $this->selectedContractId)
                ->with(['type', 'serials'])
                ->latest('purchase_date')
                ->get()
                ->toArray();
        }
    }

    public function openSerialModal($hardwareId)
    {
        $this->editingHardwareForSerial = OrganizationHardware::with(['type', 'serials'])
            ->find($hardwareId);
        $this->showSerialModal = true;
    }

    public function closeSerialModal()
    {
        $this->reset(['showSerialModal', 'editingHardwareForSerial']);
        $this->loadContractHardware(); // Refresh the contract hardware list
    }

    public function addHardwareToContract()
    {
        if ($this->selectedContractId) {
            return redirect()->route('organizations.hardware.create', [
                'organization' => $this->organization->id,
                'contract' => $this->selectedContractId
            ]);
        }
    }
}
