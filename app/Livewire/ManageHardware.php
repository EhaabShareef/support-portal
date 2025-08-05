<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\OrganizationHardware as Hardware;
use Livewire\Component;
use Livewire\WithPagination;

class ManageHardware extends Component
{
    use WithPagination;

    public Organization $organization;
    public $showForm = false;
    public $deleteId = null;

    public array $form = [
        'id' => null,
        'contract_id' => '',
        'hardware_type' => '',
        'hardware_model' => '',
        'serial_number' => '',
        'purchase_date' => '',
        'warranty_expiration' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    protected function rules()
    {
        $id = $this->form['id'] ?? 'NULL';

        return [
            'form.contract_id' => 'required|exists:organization_contracts,id',
            'form.hardware_type' => 'required|string|max:255',
            'form.hardware_model' => 'required|string|max:255',
            'form.serial_number' => 'required|string|max:255|unique:organization_hardware,serial_number,' . $id,
            'form.purchase_date' => 'required|date',
            'form.warranty_expiration' => 'required|date|after_or_equal:form.purchase_date',
            'form.remarks' => 'nullable|string',
            'form.is_active' => 'boolean',
        ];
    }

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function render()
    {
        $hardwareList = Hardware::where('org_id', $this->organization->id)
            ->with('contract')
            ->latest('purchase_date')
            ->paginate(10);

        return view('livewire.manage-hardware', compact('hardwareList'));
    }

    public function create()
    {
        $this->reset('form');
        $this->form['is_active'] = true;
        $this->showForm = true;
    }

    public function edit($id)
    {
        $hw = Hardware::findOrFail($id);

        $this->form = [
            'id' => $hw->id,
            'contract_id' => $hw->contract_id,
            'hardware_type' => $hw->hardware_type,
            'hardware_model' => $hw->hardware_model,
            'serial_number' => $hw->serial_number,
            'purchase_date' => optional($hw->purchase_date)->format('Y-m-d'),
            'warranty_expiration' => optional($hw->warranty_expiration)->format('Y-m-d'),
            'remarks' => $hw->remarks,
            'is_active' => (bool) $hw->is_active,
        ];

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = $this->form;
        $data['org_id'] = $this->organization->id;

        Hardware::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        $this->reset('showForm');
        session()->flash('message', 'Hardware saved successfully.');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }

    public function delete()
    {
        Hardware::findOrFail($this->deleteId)->delete();
        $this->reset('deleteId');
        session()->flash('message', 'Hardware deleted.');
    }
}
