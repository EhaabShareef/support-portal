<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Enums\TicketPriority;
use App\Services\TicketColorService;
use App\Models\TicketStatus as TicketStatusModel;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TicketColors extends Component
{
    public array $statusColors = [];
    public array $priorityColors = [];
    public bool $showColorResetConfirm = false;
    public string $colorResetType = '';

    public function mount()
    {
        // Load ticket colors
        $colorService = app(TicketColorService::class);
        $this->statusColors = $colorService->getStatusColors();
        $this->priorityColors = $colorService->getPriorityColors();
    }

    #[Computed]
    public function availableColors()
    {
        $colorService = app(TicketColorService::class);
        return $colorService->getColorPaletteWithValues();
    }

    #[Computed]
    public function ticketStatuses()
    {
        return TicketStatusModel::active()->ordered()->get()->map(function ($status) {
            return [
                'value' => $status->key,
                'label' => $status->name,
            ];
        });
    }

    #[Computed]
    public function ticketPriorities()
    {
        return collect(TicketPriority::cases())->map(function ($priority) {
            return [
                'value' => $priority->value,
                'label' => $priority->label(),
            ];
        });
    }

    public function saveTicketColors()
    {
        $this->checkPermission('ticket-colors.update');
        
        $colorService = app(TicketColorService::class);
        
        // Validate that all statuses and priorities have colors assigned
        foreach (TicketStatusModel::active()->get() as $status) {
            if (!isset($this->statusColors[$status->key]) || empty($this->statusColors[$status->key])) {
                $this->dispatch('flash', 'All ticket statuses must have colors assigned.', 'error');
                return;
            }
        }

        foreach (TicketPriority::cases() as $priority) {
            if (!isset($this->priorityColors[$priority->value]) || empty($this->priorityColors[$priority->value])) {
                $this->dispatch('flash', 'All ticket priorities must have colors assigned.', 'error');
                return;
            }
        }

        try {
            // Save the colors
            $colorService->updateStatusColors($this->statusColors);
            $colorService->updatePriorityColors($this->priorityColors);

            $this->dispatch('flash', 'Ticket colors updated successfully.', 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save ticket colors: ' . $e->getMessage(), 'error');
        }
    }

    public function updateStatusColor($status, $color)
    {
        $this->statusColors[$status] = $color;
    }

    public function updatePriorityColor($priority, $color)
    {
        $this->priorityColors[$priority] = $color;
    }

    public function confirmResetColors($type)
    {
        $this->checkPermission('ticket-colors.update');
        $this->colorResetType = $type;
        $this->showColorResetConfirm = true;
    }

    public function resetColorsToDefaults()
    {
        $this->checkPermission('ticket-colors.update');
        
        try {
            $colorService = app(TicketColorService::class);
            
            if ($this->colorResetType === 'status') {
                $colorService->resetStatusColorsToDefaults();
                $this->statusColors = $colorService->getStatusColors();
                $message = 'Status colors reset to defaults.';
            } elseif ($this->colorResetType === 'priority') {
                $colorService->resetPriorityColorsToDefaults();
                $this->priorityColors = $colorService->getPriorityColors();
                $message = 'Priority colors reset to defaults.';
            }

            $this->showColorResetConfirm = false;
            $this->colorResetType = '';
            $this->dispatch('flash', $message, 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to reset colors: ' . $e->getMessage(), 'error');
        }
    }

    public function cancelResetColors()
    {
        $this->showColorResetConfirm = false;
        $this->colorResetType = '';
    }

    public function getColorPreview($colorName)
    {
        $colorService = app(TicketColorService::class);
        return $colorService->getPreviewClasses($colorName);
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.ticket-colors');
    }
}