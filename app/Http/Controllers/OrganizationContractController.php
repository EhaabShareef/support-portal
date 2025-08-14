<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationContract;
use Illuminate\Http\Request;

class OrganizationContractController extends Controller
{
    public function index()
    {
        $contracts = OrganizationContract::with(['organization', 'department'])
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('contracts.create', compact('organizations', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_number' => 'required|string|max:255|unique:organization_contracts,contract_number',
            'organization_id' => 'required|exists:organizations,id',
            'department_id'   => 'required|exists:departments,id',
            'type'           => 'required|string',
            'status'         => 'required|string',
            'includes_hardware' => 'boolean',
            'is_oracle'      => 'boolean',
            'csi_number'     => 'required_if:is_oracle,true|nullable|string|max:255',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'renewal_months' => 'nullable|integer|min:1|max:120',
            'notes'          => 'nullable|string',
        ]);


        OrganizationContract::create($data);

        return redirect()
            ->route('contracts.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Contract Created!',
                'message' => 'The contract has been successfully saved.'
            ]);
    }

    public function show(OrganizationContract $contract)
    {
        $contract = OrganizationContract::with(['organization', 'department', 'hardware'])->findOrFail($contract->id);
        return view('contracts.show', compact('contract'));
    }

    public function edit(OrganizationContract $contract)
    {
        $organizations = Organization::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('contracts.edit', compact('contract', 'organizations', 'departments'));
    }

    public function update(Request $request, OrganizationContract $contract)
    {
        $data = $request->validate([
            'contract_number' => 'required|string|max:255|unique:organization_contracts,contract_number,' . $contract->id,
            'organization_id' => 'required|exists:organizations,id',
            'department_id'   => 'required|exists:departments,id',
            'type'           => 'required|string',
            'status'         => 'required|string',
            'includes_hardware' => 'boolean',
            'is_oracle'      => 'boolean',
            'csi_number'     => 'required_if:is_oracle,true|nullable|string|max:255',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'renewal_months' => 'nullable|integer|min:1|max:120',
            'notes'          => 'nullable|string',
        ]);

        $contract->update($data);

        return redirect()
            ->route('contracts.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Contract Updated!',
                'message' => 'The contract has been successfully updated.'
            ]);
    }

    public function confirmDelete(OrganizationContract $contract)
    {
        return view('contracts.confirm-delete', compact('contract'));
    }

    public function destroy(OrganizationContract $contract)
    {
        $contract->delete();

        return redirect()
        ->with('alert', [
                'type' => 'success',
                'title' => 'Contract Deleted!',
                'message' => 'The contract has been successfully deleted.'
            ]);
    }
}
