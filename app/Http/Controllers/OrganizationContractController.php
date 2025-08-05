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
            'org_id'        => 'required|exists:organizations,id',
            'department_id' => 'required|exists:departments,id',
            'is_hardware'   => 'boolean',
            'csi_remarks'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => 'required|string|in:active,expired,terminated,onhold',
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
            'org_id'        => 'required|exists:organizations,id',
            'department_id' => 'required|exists:departments,id',
            'is_hardware'   => 'boolean',
            'csi_remarks'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => 'required|string|in:active,expired,terminated,onhold',
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
