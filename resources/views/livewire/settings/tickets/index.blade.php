<div class="lg:flex">
    <nav class="lg:w-1/4 mb-4 lg:mb-0">
        <ul class="space-y-2">
            <li><button wire:click="setSection('workflow')" @class(['font-semibold' => $section === 'workflow'])>Workflow</button></li>
            <li><button wire:click="setSection('attachments')" @class(['font-semibold' => $section === 'attachments'])>Attachments</button></li>
            <li><button wire:click="setSection('priority')" @class(['font-semibold' => $section === 'priority'])>Priority</button></li>
            <li><button wire:click="setSection('status')" @class(['font-semibold' => $section === 'status'])>Status</button></li>
        </ul>
    </nav>
    <div class="lg:flex-1">
        @switch($section)
            @case('attachments')
                @livewire('settings.tickets.attachments')
                @break
            @case('priority')
                @livewire('settings.tickets.priority')
                @break
            @case('status')
                @livewire('settings.tickets.status')
                @break
            @default
                @livewire('settings.tickets.workflow')
        @endswitch
    </div>
</div>
