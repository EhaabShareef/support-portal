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
    
    // Contract Assignment Modal
    public $showContractAssignmentModal = false;
    public $selectedHardwareIds = [];
    
    // Hardware Edit Modal
    public $showEditModal = false;
    public $editingHardware = null;
    public $editForm = [
        'brand' => '',
        'model' => '',
        'quantity' => 1,
        'serial_required' => false,
    ];
    
    // Hardware Details Modal
    public $showHardwareDetailsModal = false;
    public $selectedHardware = null;
    
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
    
    public function openContractAssignmentModal()
    {
        // Pre-select all unassigned hardware
        $this->selectedHardwareIds = OrganizationHardware::where('organization_id', $this->organization->id)
            ->whereNull('contract_id')
            ->pluck('id')
            ->toArray();
        
        $this->showContractAssignmentModal = true;
    }
    
    public function closeContractAssignmentModal()
    {
        $this->reset(['showContractAssignmentModal', 'selectedHardwareIds']);
    }
    
    public function toggleHardwareSelection($hardwareId)
    {
        if (in_array($hardwareId, $this->selectedHardwareIds)) {
            $this->selectedHardwareIds = array_diff($this->selectedHardwareIds, [$hardwareId]);
        } else {
            $this->selectedHardwareIds[] = $hardwareId;
        }
    }
    
    public function selectAllUnassigned()
    {
        $this->selectedHardwareIds = OrganizationHardware::where('organization_id', $this->organization->id)
            ->whereNull('contract_id')
            ->pluck('id')
            ->toArray();
    }
    
    public function deselectAll()
    {
        $this->selectedHardwareIds = [];
    }
    
    public function assignToContract($contractId)
    {
        if (empty($this->selectedHardwareIds)) {
            session()->flash('error', 'Please select at least one hardware item to assign.');
            return;
        }

        // Assign selected hardware to the contract
        OrganizationHardware::whereIn('id', $this->selectedHardwareIds)
            ->where('organization_id', $this->organization->id)
            ->whereNull('contract_id')
            ->update(['contract_id' => $contractId]);
        
        $assignedCount = count($this->selectedHardwareIds);
        $this->closeContractAssignmentModal();
        session()->flash('message', "{$assignedCount} hardware item(s) successfully assigned to contract.");
    }
    
    public function openEditModal($hardwareId)
    {
        $this->editingHardware = OrganizationHardware::find($hardwareId);
        
        if ($this->editingHardware) {
            $this->editForm = [
                'brand' => $this->editingHardware->brand ?? '',
                'model' => $this->editingHardware->model ?? '',
                'quantity' => $this->editingHardware->quantity ?? 1,
                'serial_required' => $this->editingHardware->serial_required ?? false,
            ];
            $this->showEditModal = true;
        }
    }
    
    public function closeEditModal()
    {
        $this->reset(['showEditModal', 'editingHardware', 'editForm']);
        $this->editForm = [
            'brand' => '',
            'model' => '',
            'quantity' => 1,
            'serial_required' => false,
        ];
    }
    
    public function updateHardware()
    {
        $this->validate([
            'editForm.brand' => 'nullable|string|max:255',
            'editForm.model' => 'nullable|string|max:255',
            'editForm.quantity' => 'required|integer|min:1|max:1000',
            'editForm.serial_required' => 'boolean',
        ]);
        
        if ($this->editingHardware) {
            $this->editingHardware->update([
                'brand' => $this->editForm['brand'],
                'model' => $this->editForm['model'],
                'quantity' => $this->editForm['quantity'],
                'serial_required' => $this->editForm['serial_required'],
            ]);
            
            $this->closeEditModal();
            $this->loadContractHardware(); // Refresh contract hardware list if modal is open
            session()->flash('message', 'Hardware updated successfully.');
        }
    }
    
    public function viewHardwareDetails($hardwareId)
    {
        $this->selectedHardware = OrganizationHardware::with(['type', 'contract', 'serials'])
            ->find($hardwareId);
        $this->showHardwareDetailsModal = true;
    }
    
    public function closeHardwareDetailsModal()
    {
        $this->reset(['showHardwareDetailsModal', 'selectedHardware']);
    }
}
