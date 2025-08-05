<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $organizations = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('organizations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'company'         => 'required|string|max:255',
            'company_contact' => 'required|string|max:255',
            'tin_no'          => 'required|string|max:100',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:50',
            'active_yn'       => 'boolean',
        ]);

        Organization::create($data);

        return redirect()
            ->route('organizations.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Organization created.'
            ]);
    }

    public function show(Organization $organization)
    {
        $organization->load('contracts', 'hardware', 'users');
        return view('organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'company'         => 'required|string|max:255',
            'company_contact' => 'required|string|max:255',
            'tin_no'          => 'required|string|max:100',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:50',
            'active_yn'       => 'boolean',
        ]);

        $organization->update($data);

        return redirect()
            ->route('organizations.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Organization updated!'
            ]);
    }

    public function confirmDelete(Organization $organization)
    {
        return view('organizations.confirm-delete', compact('organization'));
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Organization deleted!'
            ]);
    }
}
