<?php

namespace App\Livewire;

use App\Models\HardwareSerial;
use App\Models\OrganizationHardware;
use Livewire\Component;

class HardwareSerialManager extends Component
{
    public int $hardwareId;
    public int $targetCount;
    public string $serialInput = '';
    public $serials;

    protected $rules = [
        'serialInput' => 'required|string|max:255',
    ];

    public function mount(int $hardwareId, int $targetCount): void
    {
        $this->hardwareId = $hardwareId;
        $this->targetCount = $targetCount;
        $this->loadSerials();
    }

    public function loadSerials(): void
    {
        $this->serials = HardwareSerial::where('organization_hardware_id', $this->hardwareId)->get();
    }

    public function addSerial(): void
    {
        $this->serialInput = trim($this->serialInput);
        $this->validate();

        if ($this->serials->count() >= $this->targetCount) {
            return;
        }

        if ($this->serials->contains('serial', $this->serialInput)) {
            return;
        }

        HardwareSerial::create([
            'organization_hardware_id' => $this->hardwareId,
            'serial' => $this->serialInput,
        ]);

        $this->serialInput = '';
        $this->loadSerials();

        if ($this->serials->count() >= $this->targetCount) {
            $this->dispatch('serialsComplete');
        }
    }

    public function removeSerial(int $id): void
    {
        HardwareSerial::where('organization_hardware_id', $this->hardwareId)->where('id', $id)->delete();
        $this->loadSerials();
    }

    public function render()
    {
        $progress = $this->serials->count();
        return view('livewire.hardware-serial-manager', [
            'progress' => $progress,
        ]);
    }
}
