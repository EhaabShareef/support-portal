{{-- resources/views/contracts/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Contract')

@section('content')
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center space-x-2">
            <x-heroicon-o-clipboard-document-check class="h-8 w-8 text-neutral-600 dark:text-neutral-300" />
            <h1 class="text-3xl font-bold">Edit Contract</h1>
        </div>
        <p class="text-neutral-600 dark:text-neutral-400 text-sm">
            Mandatory fields are marked <span class="text-red-500">*</span>
        </p>

        {{-- Form --}}
        <form action="{{ route('contracts.update', $contract) }}" method="POST" novalidate
              class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Organization --}}
                <x-forms.select name="org_id" label="Organization" :options="$organizations->pluck('name', 'id')" :value="old('org_id', $contract->org_id)" required />

                {{-- Department --}}
                <x-forms.select name="department_id" label="Department" :options="$departments->pluck('name', 'id')" :value="old('department_id', $contract->department_id)" required />

                {{-- Start Date --}}
                <x-forms.input name="start_date" label="Start Date" type="date" :value="old('start_date', $contract->start_date->format('Y-m-d'))" required />

                {{-- End Date --}}
                <x-forms.input name="end_date" label="End Date" type="date" :value="old('end_date', optional($contract->end_date)->format('Y-m-d'))" />
            </div>

            {{-- Remarks --}}
            <x-forms.input name="csi_remarks" label="CSI# / Remarks" type="text" :value="old('csi_remarks', $contract->csi_remarks)" class="col-span-2" />

            {{-- Status --}}
            <x-forms.select name="status" label="Status" 
                :options="['active' => 'Active', 'expired' => 'Expired', 'terminated' => 'Terminated', 'onhold' => 'On Hold']"
                :value="old('status', $contract->status)" required />

            {{-- Hardware Toggle --}}
            <x-forms.toggle name="is_hardware" label="Hardware Contract" :checked="old('is_hardware', $contract->is_hardware)" />

            {{-- Actions --}}
            <div class="flex space-x-4 mt-6 md:mt-12">
                <button type="submit" class="bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 rounded-md">
                    Update
                </button>
                <a href="{{ route('contracts.index') }}"
                   class="bg-neutral-200 hover:bg-neutral-300 text-neutral-800 px-4 py-2 rounded-md">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
