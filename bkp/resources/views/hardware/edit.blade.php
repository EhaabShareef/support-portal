@extends('layouts.app')

@section('title', 'Edit Hardware')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center space-x-2">
            <x-heroicon-o-cube class="h-8 w-8 text-neutral-600 dark:text-neutral-300" />
            <h1 class="text-3xl font-bold">Edit Hardware</h1>
        </div>
        <p class="text-neutral-600 dark:text-neutral-400 text-sm">
            Mandatory fields are marked <span class="text-red-500">*</span>
        </p>

        <form action="{{ route('hardware.update', $hardware) }}" method="POST" novalidate
              class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.select name="org_id" label="Organization" :options="$organizations->pluck('name', 'id')" :value="old('org_id', $hardware->org_id)" required />

                <x-forms.select name="contract_id" label="Contract"
                    :options="$contracts->mapWithKeys(fn($c) => [$c->id => $c->organization->name . ' - ' . $c->department->name])"
                    :value="old('contract_id', $hardware->contract_id)" required />

                <x-forms.input name="hardware_type" label="Hardware Type" :value="old('hardware_type', $hardware->hardware_type)" required />
                <x-forms.input name="hardware_model" label="Hardware Model" :value="old('hardware_model', $hardware->hardware_model)" required />
                <x-forms.input name="serial_number" label="Serial Number" :value="old('serial_number', $hardware->serial_number)" required />
                <x-forms.input name="purchase_date" label="Purchase Date" type="date" :value="old('purchase_date', optional($hardware->purchase_date)->format('Y-m-d'))" />
                <x-forms.input name="warranty_expiration" label="Warranty Expiry" type="date" :value="old('warranty_expiration', optional($hardware->warranty_expiration)->format('Y-m-d'))" />
            </div>

            <x-forms.input name="remarks" label="Remarks" :value="old('remarks', $hardware->remarks)" class="col-span-2" />

            <x-forms.toggle name="is_active" label="Hardware is Active" :checked="old('is_active', $hardware->is_active)" />

            <div class="flex space-x-4 mt-6 md:mt-12">
                <button type="submit" class="bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 rounded-md">
                    Update
                </button>
                <a href="{{ route('hardware.index') }}"
                   class="bg-neutral-200 hover:bg-neutral-300 text-neutral-800 px-4 py-2 rounded-md">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection