<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Organization;
use App\Models\OrganizationContract as Contract;
use Livewire\WithPagination;

class ManageContracts extends Component
{
    use WithPagination;

    public Organization $organization;

    public $showForm = false;
    public $deleteId = null;

    public array $form = [
        'id' => null,
        'department_id' => '',
        'start_date' => '',
        'end_date' => '',
        'status' => 'active',
        'csi_remarks' => '',
        'is_hardware' => false,
    ];

    protected $rules = [
        'form.department_id' => 'required|exists:departments,id',
        'form.start_date' => 'required|date',
        'form.end_date' => 'nullable|date|after_or_equal:form.start_date',
        'form.status' => 'required|in:active,inactive',
        'form.csi_remarks' => 'nullable|string|max:255',
        'form.is_hardware' => 'boolean',
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function render()
    {
        $contracts = Contract::where('org_id', $this->organization->id)
            ->with('department')
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('livewire.manage-contracts', compact('contracts'));
    }

    public function create()
    {
        $this->reset('form');

        $this->form['status'] = 'active';
        $this->form['is_hardware'] = false;
        $this->form['org_id'] = $this->organization->id;
        $this->showForm = true;
    }

    public function edit($id)
    {
        $contract = Contract::findOrFail($id);

        $this->form = [
            'id' => $contract->id,
            'department_id' => $contract->department_id,
            'start_date' => optional($contract->start_date)->format('Y-m-d'),
            'end_date' => optional($contract->end_date)->format('Y-m-d'),
            'status' => $contract->status,
            'csi_remarks' => $contract->csi_remarks,
            'is_hardware' => (bool) $contract->is_hardware,
        ];

        $this->showForm = true;
    }


    public function save()
    {
        $this->validate();

        $data = $this->form;
        $data['organization_id'] = $this->organization->id;

        Contract::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        $this->reset('showForm');
        session()->flash('message', 'Contract saved successfully.');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }

    public function delete()
    {
        Contract::findOrFail($this->deleteId)->delete();
        $this->reset('deleteId');
        session()->flash('message', 'Contract deleted.');
    }
}
