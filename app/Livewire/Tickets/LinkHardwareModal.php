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
    public array $availableHardware = [];
    public array $availableSerials = [];

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
        } else {
            $this->resetForm();
        }
    }

    public function loadAvailableHardware(): void
    {
        $query = OrganizationHardware::where('organization_id', $this->ticket->organization_id)
            ->whereNull('deleted_at');

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

    public function updatedSearch(): void
    {
        $this->loadAvailableHardware();
    }

    public function selectHardware($hardwareId): void
    {
        $this->selectedHardwareId = $hardwareId;
        $this->selectedSerialId = null;
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

    public function linkHardware(): void
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

        // Link hardware to ticket
        $this->ticket->hardware()->syncWithoutDetaching([
            $this->selectedHardwareId => [
                'maintenance_note' => $this->maintenanceNote
            ]
        ]);

        // Update maintenance timestamps on hardware
        if (!empty($this->maintenanceNote)) {
            $hardware->update([
                'last_maintenance' => now(),
                'next_maintenance' => now()->addMonths(6) // Default 6 months, can be configurable
            ]);
        }

        session()->flash('message', 'Hardware linked successfully!');
        $this->dispatch('ticket:refresh');
        $this->toggle();
    }

    public function unlinkHardware($hardwareId): void
    {
        $this->authorize('update', $this->ticket);

        $this->ticket->hardware()->detach($hardwareId);
        session()->flash('message', 'Hardware unlinked successfully!');
        $this->dispatch('ticket:refresh');
    }

    public function updateMaintenanceNote($hardwareId, $note): void
    {
        $this->authorize('update', $this->ticket);

        $hardware = OrganizationHardware::find($hardwareId);
        if (!$hardware) {
            session()->flash('error', 'Hardware not found.');
            return;
        }

        // Update the maintenance note
        $this->ticket->hardware()->updateExistingPivot($hardwareId, [
            'maintenance_note' => $note
        ]);

        // Update maintenance timestamps if note is provided
        if (!empty($note)) {
            $hardware->update([
                'last_maintenance' => now(),
                'next_maintenance' => now()->addMonths(6) // Default 6 months
            ]);
        }

        session()->flash('message', 'Maintenance note updated successfully!');
        $this->dispatch('ticket:refresh');
    }

    public function resetForm(): void
    {
        $this->search = '';
        $this->selectedHardwareId = null;
        $this->selectedSerialId = null;
        $this->maintenanceNote = '';
        $this->availableHardware = [];
        $this->availableSerials = [];
    }

    public function render()
    {
        return view('livewire.tickets.link-hardware-modal');
    }
}

