<div class="space-y-4">
    @if($step === 'contract')
        <livewire:hardware-contract-selector :organization-id="$organization->id" />
    @elseif($step === 'hardware' && $contractId)
        <livewire:hardware-form-simple :organization-id="$organization->id" :contract-id="$contractId" />
    @elseif($step === 'serials' && $hardwareId)
        <livewire:hardware-serial-manager :hardware-id="$hardwareId" :target-count="$quantity" />
    @else
        <div class="p-4 text-sm text-neutral-700 dark:text-neutral-300">Hardware entry complete.</div>
    @endif
</div>
