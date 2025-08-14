<?php

namespace App\Livewire;

use App\Models\HardwareSerial;
use App\Models\OrganizationHardware;
use Livewire\Component;

class HardwareMultiSerialManager extends Component
{
    public array $hardwareItems = [];
    public array $serialInputs = []; // Track serial inputs per hardware item
    public array $existingSerials = []; // Track existing serials per hardware item
    public int $currentHardwareIndex = 0;

    protected $rules = [
        'serialInputs.*' => 'required|string|max:255',
    ];

    public function mount(array $hardwareItems): void
    {
        // Only include hardware that requires serials
        $this->hardwareItems = collect($hardwareItems)->filter(function ($item) {
            return $item['serial_required'];
        })->values()->toArray();

        // Initialize serial inputs and load existing serials for each hardware
        foreach ($this->hardwareItems as $index => $item) {
            $this->serialInputs[$index] = '';
            $this->loadExistingSerials($index);
        }
    }

    public function loadExistingSerials(int $hardwareIndex): void
    {
        if (isset($this->hardwareItems[$hardwareIndex])) {
            $hardwareId = $this->hardwareItems[$hardwareIndex]['id'];
            $this->existingSerials[$hardwareIndex] = HardwareSerial::where('organization_hardware_id', $hardwareId)
                ->get()
                ->pluck('serial', 'id')
                ->toArray();
        }
    }

    public function addSerial(int $hardwareIndex): void
    {
        $this->validate([
            "serialInputs.$hardwareIndex" => 'required|string|max:255',
        ]);

        $hardware = $this->hardwareItems[$hardwareIndex];
        $serialValue = trim($this->serialInputs[$hardwareIndex]);

        // Check if we've reached the quantity limit
        if (count($this->existingSerials[$hardwareIndex]) >= $hardware['quantity']) {
            session()->flash("error_$hardwareIndex", 'All serial numbers for this hardware have been added.');
            return;
        }

        // Check for duplicate serials within this hardware
        if (in_array($serialValue, $this->existingSerials[$hardwareIndex])) {
            session()->flash("error_$hardwareIndex", 'This serial number already exists for this hardware.');
            return;
        }

        // Check for duplicate serials globally (optional - you can remove this if not needed)
        $globalDuplicate = HardwareSerial::where('serial', $serialValue)->exists();
        if ($globalDuplicate) {
            session()->flash("error_$hardwareIndex", 'This serial number is already used in the system.');
            return;
        }

        // Add the serial number
        $serial = HardwareSerial::create([
            'organization_hardware_id' => $hardware['id'],
            'serial' => $serialValue,
        ]);

        // Update local tracking
        $this->existingSerials[$hardwareIndex][$serial->id] = $serialValue;
        $this->serialInputs[$hardwareIndex] = '';

        // Clear any previous error for this hardware
        session()->forget("error_$hardwareIndex");
        session()->flash("success_$hardwareIndex", 'Serial number added successfully!');
    }

    public function removeSerial(int $hardwareIndex, int $serialId): void
    {
        HardwareSerial::find($serialId)?->delete();
        unset($this->existingSerials[$hardwareIndex][$serialId]);
        session()->flash("success_$hardwareIndex", 'Serial number removed successfully!');
    }

    public function isHardwareComplete(int $hardwareIndex): bool
    {
        $hardware = $this->hardwareItems[$hardwareIndex];
        return count($this->existingSerials[$hardwareIndex]) >= $hardware['quantity'];
    }

    public function allHardwareComplete(): bool
    {
        foreach ($this->hardwareItems as $index => $hardware) {
            if (!$this->isHardwareComplete($index)) {
                return false;
            }
        }
        return true;
    }

    public function continue(): void
    {
        if ($this->allHardwareComplete()) {
            $this->dispatch('serialsCompleted');
        } else {
            session()->flash('error', 'Please complete serial numbers for all hardware items before continuing.');
        }
    }

    public function skipToComplete(): void
    {
        $this->dispatch('serialsCompleted');
    }

    public function render()
    {
        return view('livewire.hardware-multi-serial-manager');
    }
}