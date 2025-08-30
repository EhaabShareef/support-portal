<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\OrganizationSubscriptionStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsOrganization extends Component
{
    // Subscription Status Management
    public bool $showStatusModal = false;
    public bool $statusEditMode = false;
    public ?int $selectedStatusId = null;
    public array $statusForm = [
        'key' => '',
        'label' => '',
        'color' => '#3b82f6',
        'sort_order' => 0,
        'is_active' => true,
        'description' => '',
    ];

    // Delete confirmations
    public ?int $confirmingStatusDelete = null;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
    }

    public function refreshData()
    {
        // Data loaded via computed properties
    }

    #[Computed]
    public function subscriptionStatuses()
    {
        return OrganizationSubscriptionStatus::ordered()->get();
    }

    // Subscription Status Methods
    public function createStatus()
    {
        $this->checkPermission('settings.update');
        $this->resetStatusForm();
        $this->statusEditMode = false;
        $this->showStatusModal = true;
    }

    public function editStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = OrganizationSubscriptionStatus::find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Subscription status not found.');
            return;
        }

        $this->selectedStatusId = $id;
        $this->statusForm = [
            'key' => $status->key,
            'label' => $status->label,
            'color' => $status->color,
            'sort_order' => $status->sort_order,
            'is_active' => $status->is_active,
            'description' => $status->description,
        ];
        $this->statusEditMode = true;
        $this->showStatusModal = true;
    }

    public function saveStatus()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'statusForm.key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                $this->statusEditMode 
                    ? 'unique:organization_subscription_statuses,key,' . $this->selectedStatusId
                    : 'unique:organization_subscription_statuses,key',
            ],
            'statusForm.label' => 'required|string|max:255',
            'statusForm.color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'statusForm.sort_order' => 'integer|min:0',
            'statusForm.is_active' => 'boolean',
            'statusForm.description' => 'nullable|string',
        ]);

        try {
            if ($this->statusEditMode) {
                $status = OrganizationSubscriptionStatus::findOrFail($this->selectedStatusId);
                $status->update($this->statusForm);
                $message = 'Subscription status updated successfully.';
            } else {
                OrganizationSubscriptionStatus::create($this->statusForm);
                $message = 'Subscription status created successfully.';
            }

            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save subscription status: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = OrganizationSubscriptionStatus::withCount('organizations')->find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Subscription status not found.');
            return;
        }
        
        if ($status->organizations_count > 0) {
            $this->dispatch('error', 'Cannot delete subscription status that is in use by organizations.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            OrganizationSubscriptionStatus::findOrFail($this->confirmingStatusDelete)->delete();
            $this->confirmingStatusDelete = null;
            $this->dispatch('saved', 'Subscription status deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete subscription status: ' . $e->getMessage());
        }
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->resetStatusForm();
    }

    public function cancelStatusDelete()
    {
        $this->confirmingStatusDelete = null;
    }

    public function toggleStatusActive($id)
    {
        $this->checkPermission('settings.update');
        
        try {
            $status = OrganizationSubscriptionStatus::findOrFail($id);
            $status->update(['is_active' => !$status->is_active]);
            
            $statusText = $status->is_active ? 'enabled' : 'disabled';
            $this->dispatch('saved', "Subscription status {$statusText} successfully.");
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update subscription status: ' . $e->getMessage());
        }
    }

    // Helper Methods
    private function resetStatusForm()
    {
        $this->statusForm = [
            'key' => '',
            'label' => '',
            'color' => '#3b82f6',
            'sort_order' => $this->subscriptionStatuses->count() + 1,
            'is_active' => true,
            'description' => '',
        ];
        $this->selectedStatusId = null;
        $this->resetErrorBag('statusForm');
    }

    public function updatedStatusFormLabel()
    {
        if (!$this->statusEditMode) {
            $this->statusForm['key'] = strtolower(str_replace([' ', '-'], '_', $this->statusForm['label']));
            $this->statusForm['key'] = preg_replace('/[^a-z0-9_]/', '', $this->statusForm['key']);
        }
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.organization');
    }
}