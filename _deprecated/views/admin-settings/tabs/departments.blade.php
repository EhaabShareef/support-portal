<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Departments</h3>
        <button wire:click="createDept" 
            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            New Department
        </button>
    </div>

    {{-- Departments List --}}
    @if($this->departments->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->departments as $department)
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">{{ $department->name }}</h4>
                        <div class="flex items-center space-x-1">
                            <button wire:click="editDept({{ $department->id }})" 
                                class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1">
                                <x-heroicon-o-pencil-square class="h-4 w-4" />
                            </button>
                            <button wire:click="confirmDeleteDept({{ $department->id }})" 
                                class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    
                    @if($department->description)
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ $department->description }}</p>
                    @endif
                    
                    @if($department->departmentGroup)
                        <div class="flex items-center mb-3">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $department->departmentGroup->color }}"></div>
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $department->departmentGroup->name }}</span>
                        </div>
                    @endif
                    
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-500 dark:text-neutral-400">
                            {{ $department->users_count }} {{ Str::plural('user', $department->users_count) }}, 
                            {{ $department->tickets_count }} {{ Str::plural('ticket', $department->tickets_count) }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $department->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 text-neutral-500 dark:text-neutral-400">
            <x-heroicon-o-building-office class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No departments found</p>
        </div>
    @endif

    {{-- Modals will be added here --}}
    {{-- TODO: Add create/edit modal --}}
    {{-- TODO: Add delete confirmation modal --}}
</div>