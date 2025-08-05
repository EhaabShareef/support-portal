@extends('layouts.app')

@section('title', 'New Contract')

@section('content')
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center space-x-2">
            <x-heroicon-o-clipboard-document-check class="h-8 w-8 text-neutral-600 dark:text-neutral-300" />
            <h1 class="text-3xl font-bold">New Contract</h1>
        </div>
        <p class="text-neutral-600 dark:text-neutral-400 text-sm">
            Mandatory fields are marked <span class="text-red-500">*</span>
        </p>

        {{-- Form --}}
        <form action="{{ route('contracts.store') }}" method="POST" novalidate
            class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Organization --}}
                <x-forms.select name="org_id" label="Organization" :options="$organizations->pluck('name', 'id')" required />

                {{-- Department --}}
                <x-forms.select name="department_id" label="Department" :options="$departments->pluck('name', 'id')" :value="old('department_id')" required />

                {{-- Start Date --}}
                <x-forms.input name="start_date" label="Start Date" type="date" required />

                {{-- End Date --}}
                <x-forms.input name="end_date" label="End Date" type="date" />
            </div>

            {{-- Remarks --}}
            <x-forms.input name="csi_remarks" label="CSI# / Remarks" type="text" class="col-span-2" />

            <x-forms.select name="status" label="Status" :options="[
                'active' => 'Active',
                'expired' => 'Expired',
                'terminated' => 'Terminated',
                'onhold' => 'On Hold',
            ]" required />

            {{-- Hardware Toggle --}}
            <x-forms.toggle name="is_hardware" label="Hardware Contract" />


            {{-- Actions --}}
            <div class="flex space-x-4 mt-6 md:mt-12">
                <button type="submit" class="bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 rounded-md">
                    Save
                </button>
                <a href="{{ route('contracts.index') }}"
                    class="bg-neutral-200 hover:bg-neutral-300 text-neutral-800 px-4 py-2 rounded-md">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
