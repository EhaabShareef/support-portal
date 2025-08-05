<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="flex text-xl items-center font-semibold text-neutral-800 dark:text-neutral-100">
            <x-heroicon-o-cpu-chip class="inline h-8 w-8 mr-2" />
            Manage Hardware – {{ $organization->name }}
        </h1>

        <div class="flex items-center space-x-2">
            <a href="{{ route('organizations.show', ['organization' => $organization->id]) }}"
                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md">
                <x-heroicon-o-arrow-left class="inline h-4 w-4 mr-1" /> Back
            </a>

            <button wire:click="create" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md">
                <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" /> New Hardware
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-900 px-4 py-2 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- Form --}}
    @if ($showForm)
        <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/1 space-y-4">
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                {{ $form['id'] ? 'Edit Hardware' : 'Add Hardware' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Contract</label>
                    <select wire:model.defer="form.contract_id"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm">
                        <option value="">Select Contract</option>
                        @foreach (\App\Models\OrganizationContract::where('org_id', $organization->id)->get() as $contract)
                            <option value="{{ $contract->id }}">{{ $contract->department->name ?? 'N/A' }} ({{ $contract->status }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Hardware Type</label>
                    <input type="text" wire:model.defer="form.hardware_type"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Hardware Model</label>
                    <input type="text" wire:model.defer="form.hardware_model"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Serial Number</label>
                    <input type="text" wire:model.defer="form.serial_number"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Purchase Date</label>
                    <input type="date" wire:model.defer="form.purchase_date"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Warranty Expiration</label>
                    <input type="date" wire:model.defer="form.warranty_expiration"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm text-neutral-700 dark:text-neutral-300">Remarks</label>
                    <textarea wire:model.defer="form.remarks"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-white/30 bg-white/60 dark:bg-neutral-900/40 text-sm"></textarea>
                </div>
                <div class="flex items-center space-x-2 md:col-span-2">
                    <input type="checkbox" wire:model.defer="form.is_active"
                        class="rounded border-white/30 bg-white/60 dark:bg-neutral-800 text-sky-500 focus:ring-sky-500" />
                    <span class="text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                </div>
            </div>

            <div class="flex space-x-2 mt-4">
                <button wire:click="save" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-sm">
                    Save
                </button>
                <button wire:click="$set('showForm', false)"
                    class="px-4 py-2 bg-neutral-500 hover:bg-neutral-600 text-white rounded-md text-sm">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Hardware List --}}
    <div class="space-y-4">
        @forelse ($hardwareList as $hw)
            <div wire:key="hw-{{ $hw->id }}"
                class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/10 transition-all hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-base font-semibold text-neutral-800 dark:text-neutral-100">
                            {{ $hw->hardware_type }} / {{ $hw->hardware_model }}
                            @if ($hw->is_active)
                                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                            SN: {{ $hw->serial_number }}<br>
                            Purchased: {{ \Carbon\Carbon::parse($hw->purchase_date)->format('d-m-Y') }}<br>
                            Warranty Exp: {{ \Carbon\Carbon::parse($hw->warranty_expiration)->format('d-m-Y') }}
                        </div>
                        @if ($hw->remarks)
                            <div class="text-xs mt-1 text-neutral-700 dark:text-neutral-300">
                                Remarks: {{ $hw->remarks }}
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col space-y-1 text-xs text-right">
                        <button wire:click="edit({{ $hw->id }})"
                            class="text-sky-500 hover:text-sky-700 hover:bg-sky-200 rounded-md transition-all">
                            <x-heroicon-o-cog-8-tooth class="inline size-8 p-1 stroke-1" />
                        </button>
                        <button wire:click="confirmDelete({{ $hw->id }})"
                            class="text-red-500 hover:text-red-700 hover:bg-red-200/70 rounded-md transition">
                            <x-heroicon-o-trash class="inline size-8 p-1 stroke-1" />
                        </button>
                    </div>
                </div>
                @if ($deleteId === $hw->id)
                    <div class="mt-2 text-sm text-red-500 py-3">
                        Are you sure?
                        <button wire:click="delete"
                            class="rounded-md px-2 text-red-700 hover:bg-red-200 font-semibold transition-all">Yes</button>
                        <button wire:click="$set('deleteId', null)"
                            class="rounded-md px-2 text-neutral-300 bg-neutral-700 hover:bg-neutral-400">Cancel</button>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-neutral-500 dark:text-neutral-400 text-sm">No hardware found.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-4">
        {{ $hardwareList->links('vendor.pagination.tailwind') }}
    </div>
</div>