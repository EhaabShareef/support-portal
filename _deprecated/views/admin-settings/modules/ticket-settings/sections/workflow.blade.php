<x-settings.section title="Workflow Configuration" description="Configure how tickets behave and flow through the system">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Default Reply Status --}}
        <div>
            <label for="defaultReplyStatus" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Default Reply Status
            </label>
            <select 
                wire:model="defaultReplyStatus" 
                id="defaultReplyStatus"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            >
                <option value="in_progress">In Progress</option>
                <option value="pending">Pending</option>
                <option value="waiting_for_customer">Waiting for Customer</option>
                <option value="waiting_for_third_party">Waiting for Third Party</option>
            </select>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Status to set when a support agent replies to a ticket
            </p>
        </div>

        {{-- Reopen Window Days --}}
        <div>
            <label for="reopenWindowDays" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Reopen Window (Days)
            </label>
            <input 
                type="number" 
                wire:model="reopenWindowDays" 
                id="reopenWindowDays"
                min="1" 
                max="30"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            />
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Number of days after which closed tickets can be reopened
            </p>
        </div>

        {{-- Message Order --}}
        <div>
            <label for="messageOrder" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Message Display Order
            </label>
            <select 
                wire:model="messageOrder" 
                id="messageOrder"
                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
            >
                <option value="newest_first">Newest First</option>
                <option value="oldest_first">Oldest First</option>
            </select>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                How messages are displayed in ticket conversations
            </p>
        </div>

        {{-- Escalation Confirmation --}}
        <div>
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    wire:model="requireEscalationConfirmation" 
                    id="requireEscalationConfirmation"
                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                />
                <label for="requireEscalationConfirmation" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    Require Escalation Confirmation
                </label>
            </div>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Require confirmation when escalating tickets to higher priority
            </p>
        </div>
    </div>
</x-settings.section>
