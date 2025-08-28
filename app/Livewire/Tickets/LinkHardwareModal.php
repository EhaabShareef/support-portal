<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\OrganizationHardware;
use App\Models\HardwareSerial;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class LinkHardwareModal extends Component
{
    use AuthorizesRequests, WithPagination;

    public Ticket $ticket;
    public bool $show = false;
    public string $search = '';
    public ?int $selectedHardwareId = null;
    public ?int $selectedSerialId = null;
    public string $maintenanceNote = '';
    public int $quantity = 1;
    public array $availableHardware = [];
    public array $availableSerials = [];
    public array $linkedHardwareNotes = [];
    public array $linkedHardwareQuantities = [];

    protected $listeners = ['link-hardware:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->authorize('view', $this->ticket);
    }

    public function toggle(): void
    {
        $this->authorize('update', $this->ticket);

        if ($this->ticket->isClosed()) {
            return;
        }

        $this->show = ! $this->show;
        
        if ($this->show) {
            $this->loadAvailableHardware();
            $this->loadLinkedHardwareData();
        } else {
            $this->resetForm();
        }
    }

    public function loadAvailableHardware(): void
    {
        // Get IDs of already linked hardware
        $linkedHardwareIds = $this->ticket->hardware->pluck('id')->toArray();

        $query = OrganizationHardware::where('organization_id', $this->ticket->organization_id)
            ->whereNull('deleted_at')
            ->whereNotIn('id', $linkedHardwareIds); // Exclude already linked hardware

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_tag', 'like', '%' . $this->search . '%');
            });
        }

        $this->availableHardware = $query->with(['type', 'serials'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get()
            ->toArray();
    }

    public function loadLinkedHardwareData(): void
    {
        // Load existing maintenance notes and quantities for linked hardware
        foreach ($this->ticket->hardware as $hardware) {
            $this->linkedHardwareNotes[$hardware->id] = $hardware->pivot->maintenance_note ?? '';
            $this->linkedHardwareQuantities[$hardware->id] = $hardware->pivot->quantity ?? 1;
        }
    }

    public function updatedSearch(): void
    {
        $this->loadAvailableHardware();
    }

    public function selectHardware($hardwareId): void
    {
        $this->selectedHardwareId = $hardwareId;
        $this->selectedSerialId = null;
        $this->maintenanceNote = '';
        $this->quantity = 1;
        $this->loadSerialsForHardware($hardwareId);
    }

    public function loadSerialsForHardware($hardwareId): void
    {
        $hardware = OrganizationHardware::find($hardwareId);
        if ($hardware && $hardware->serial_required) {
            $this->availableSerials = $hardware->serials()
                ->orderBy('serial')
                ->get()
                ->toArray();
        } else {
            $this->availableSerials = [];
        }
    }

    public function selectSerial($serialId): void
    {
        $this->selectedSerialId = $serialId;
    }

    public function addToTicket(): void
    {
        $this->authorize('update', $this->ticket);

        if (!$this->selectedHardwareId) {
            session()->flash('error', 'Please select a hardware item.');
            return;
        }

        $hardware = OrganizationHardware::find($this->selectedHardwareId);
        if (!$hardware) {
            session()->flash('error', 'Selected hardware not found.');
            return;
        }

        // Check if hardware requires serial and one is selected
        if ($hardware->serial_required && !$this->selectedSerialId) {
            session()->flash('error', 'This hardware requires a serial number selection.');
            return;
        }

        // Validate quantity
        if ($this->quantity < 1 || $this->quantity > $hardware->quantity) {
            session()->flash('error', "Quantity must be between 1 and {$hardware->quantity}.");
            return;
        }

        // Link hardware to ticket
        $this->ticket->hardware()->syncWithoutDetaching([
            $this->selectedHardwareId => [
                'maintenance_note' => $this->maintenanceNote,
                'quantity' => $this->quantity
            ]
        ]);

        // Update maintenance timestamps on hardware if note is provided
        if (!empty($this->maintenanceNote)) {
            $hardware->update([
                'last_maintenance' => now(),
                'next_maintenance' => now()->addMonths(6) // Default 6 months, can be configurable
            ]);
        }

        session()->flash('message', 'Hardware added to ticket successfully!');
        $this->dispatch('ticket:refresh');
        
        // Reset selection and reload data
        $this->selectedHardwareId = null;
        $this->selectedSerialId = null;
        $this->maintenanceNote = '';
        $this->quantity = 1;
        $this->loadAvailableHardware();
        $this->loadLinkedHardwareData();
    }

    public function updateLinkedHardware($hardwareId): void
    {
        $this->authorize('update', $this->ticket);

        $hardware = OrganizationHardware::find($hardwareId);
        if (!$hardware) {
            session()->flash('error', 'Hardware not found.');
            return;
        }

        $quantity = $this->linkedHardwareQuantities[$hardwareId] ?? 1;
        $note = $this->linkedHardwareNotes[$hardwareId] ?? '';

        // Validate quantity
        if ($quantity < 1 || $quantity > $hardware->quantity) {
            session()->flash('error', "Quantity must be between 1 and {$hardware->quantity}.");
            return;
        }

        // Update the hardware link
        $this->ticket->hardware()->updateExistingPivot($hardwareId, [
            'maintenance_note' => $note,
            'quantity' => $quantity
        ]);

        // Update maintenance timestamps if note is provided
        if (!empty($note)) {
            $hardware->update([
                'last_maintenance' => now(),
                'next_maintenance' => now()->addMonths(6) // Default 6 months
            ]);
        }

        session()->flash('message', 'Hardware details updated successfully!');
        $this->dispatch('ticket:refresh');
    }

    public function unlinkHardware($hardwareId): void
    {
        $this->authorize('update', $this->ticket);

        $this->ticket->hardware()->detach($hardwareId);
        session()->flash('message', 'Hardware removed from ticket successfully!');
        $this->dispatch('ticket:refresh');
        
        // Reload data after unlinking
        $this->loadAvailableHardware();
        $this->loadLinkedHardwareData();
    }

    public function resetForm(): void
    {
        $this->search = '';
        $this->selectedHardwareId = null;
        $this->selectedSerialId = null;
        $this->maintenanceNote = '';
        $this->quantity = 1;
        $this->availableHardware = [];
        $this->availableSerials = [];
        $this->linkedHardwareNotes = [];
        $this->linkedHardwareQuantities = [];
    }

    public function render()
    {
        return view('livewire.tickets.link-hardware-modal');
    }
}

