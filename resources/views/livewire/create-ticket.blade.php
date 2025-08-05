<div class="max-w-4xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="page-header">
        <h1 class="page-title">
            <x-heroicon-o-ticket class="h-7 w-7" />
            Create Support Ticket
        </h1>

        <a href="{{ route('tickets.index') }}" class="btn-secondary">
            <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" />
            Back
        </a>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    {{-- Ticket Form --}}
    <div class="content-section">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="form-label">Subject</label>
                <input type="text" wire:model.defer="form.subject" class="form-input" />
                @error('form.subject') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Type</label>
                <select wire:model.defer="form.type" class="form-select">
                    <option value="issue">Issue</option>
                    <option value="feedback">Feedback</option>
                    <option value="bug">Bug</option>
                    <option value="lead">Lead</option>
                    <option value="task">Task</option>
                </select>
            </div>

            <div>
                <label class="form-label">Organization</label>
                <select wire:model.defer="form.org_id" class="form-select">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Department</label>
                <select wire:model.defer="form.dept_id" class="form-select">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Owner</label>
                <select wire:model.defer="form.owner_id" class="form-select">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Priority</label>
                <select wire:model.defer="form.priority" class="form-select">
                    @foreach(['Low', 'Normal', 'High', 'Serious Business Impact'] as $priority)
                        <option value="{{ $priority }}">{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Submit --}}
        <div class="pt-4 border-t border-white/20 flex justify-end">
            <button wire:click="submit" class="btn-primary">
                <x-heroicon-o-check class="w-5 h-5 mr-1" />
                Submit Ticket
            </button>
        </div>
    </div>
</div>
