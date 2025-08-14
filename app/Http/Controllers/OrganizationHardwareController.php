<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use Illuminate\Http\Request;

class OrganizationHardwareController extends Controller
{
    public function index()
    {
        $hardware = OrganizationHardware::with(['organization', 'contract'])
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);

        return view('hardware.index', compact('hardware'));
    }

    public function create()
    {
        $organizations = Organization::whereHas('contracts', function ($query) {
            $query->where('includes_hardware', true);
        })->orderBy('name')->get();

        $contracts = OrganizationContract::where('includes_hardware', true)
            ->orderBy('start_date', 'desc')
            ->with(['organization', 'department'])
            ->get();

        return view('hardware.create', compact('organizations', 'contracts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id'         => 'required|exists:organization_contracts,id',
            'hardware_type'       => 'required|string|max:255',
            'brand'              => 'nullable|string|max:255',
            'model'              => 'nullable|string|max:255',
            'serial_number'       => 'nullable|string|max:255|unique:organization_hardware,serial_number',
            'purchase_date'       => 'nullable|date',
            'location'            => 'nullable|string|max:255',
            'remarks'             => 'nullable|string',
            'last_maintenance'    => 'nullable|date',
            'next_maintenance'    => 'nullable|date',
        ]);

        $contract = OrganizationContract::findOrFail($data['contract_id']);
        $data['organization_id'] = $contract->organization_id;

        OrganizationHardware::create($data);

        return redirect()
            ->route('hardware.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Hardware record created.'
            ]);
    }

    public function show(OrganizationHardware $hardware)
    {
        return view('hardware.show', ['hardware' => $hardware->load(['organization', 'contract'])]);
    }

    public function edit(OrganizationHardware $hardware)
    {
        $organizations = Organization::orderBy('name')->get();
        $contracts     = OrganizationContract::orderBy('start_date', 'desc')->get();

        return view('hardware.edit', compact('hardware', 'organizations', 'contracts'));
    }

    public function update(Request $request, OrganizationHardware $hardware)
    {
        $data = $request->validate([
            'organization_id'     => 'required|exists:organizations,id',
            'contract_id'         => 'nullable|exists:organization_contracts,id',
            'hardware_type'       => 'required|string|max:255',
            'brand'              => 'nullable|string|max:255',
            'model'              => 'nullable|string|max:255',
            'serial_number'       => "nullable|string|max:255|unique:organization_hardware,serial_number,{$hardware->id}",
            'purchase_date'       => 'nullable|date',
            'location'            => 'nullable|string|max:255',
            'remarks'             => 'nullable|string',
            'last_maintenance'    => 'nullable|date',
            'next_maintenance'    => 'nullable|date',
        ]);

        $hardware->update($data);

        return redirect()
            ->route('hardware.index')
            ->with('success', 'Hardware record updated.');
    }

    public function confirmDelete(OrganizationHardware $hardware)
    {
        return view('hardware.confirm-delete', compact('hardware'));
    }

    public function destroy(OrganizationHardware $hardware)
    {
        $hardware->delete();

        return redirect()
            ->route('hardware.index')
            ->with('success', 'Hardware record deleted.');
    }
}
