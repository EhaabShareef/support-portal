@extends('layouts.app')

@section('title', 'New Organization')

@section('content')
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center space-x-2">
            <x-heroicon-o-building-office-2 class="h-8 w-8 text-neutral-600 dark:text-neutral-300" />
            <h1 class="text-3xl font-bold">New Organization</h1>
        </div>
        <p class="text-neutral-600 dark:text-neutral-400 text-sm">Mandatory fields are marked <span
                class="text-red-500">*</span></p>

        {{-- Form Card --}}
        <form action="{{ route('organizations.store') }}" method="POST" novalidate
            class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 space-y-4 shadow-lg">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input name="name" label="Name" required />
                <x-forms.input name="company" label="Parent Company" required />
                <x-forms.input name="company_contact" label="Contact Person" required />
                <x-forms.input name="tin_no" label="TIN No." required />
                <x-forms.input name="email" label="Email" type="email" required />
                <x-forms.input name="phone" label="Phone" required />
            </div>

            <x-forms.toggle name="active_yn" label="Active" checked />

            <div class="flex space-x-4 mt-6 md:mt-12">
                <button type="submit" class="bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 rounded-md">
                    Save
                </button>
                <a href="{{ route('organizations.index') }}"
                    class="bg-neutral-200 hover:bg-neutral-300 text-neutral-800 px-4 py-2 rounded-md">
                    Cancel
                </a>
            </div>
        </form>

    </div>
@endsection
