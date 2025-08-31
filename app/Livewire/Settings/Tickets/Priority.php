<?php

namespace App\Livewire\Settings\Tickets;

use Livewire\Component;
use App\Services\TicketColorService;
use App\Enums\TicketPriority;

class Priority extends Component
{
    public $priorityColors = [];
    public $selectedPriority = null;
    public $selectedBgColor = '#f3f4f6';
    public $selectedTextColor = '#374151';
    public $customBgColor = '#f3f4f6';
    public $customTextColor = '#374151';
    public $showCustomColorPicker = false;
    
    // UI state
    public $showFlash = false;
    public $flashMessage = '';
    public $flashType = 'success';
    public $showColorPicker = false;

    protected $ticketColorService;

    public function boot(TicketColorService $ticketColorService)
    {
        $this->ticketColorService = $ticketColorService;
    }

    public function mount()
    {
        $this->loadPriorityColors();
    }

    public function loadPriorityColors()
    {
        $this->priorityColors = $this->ticketColorService->getPriorityColors();
    }

    public function selectPriority($priority)
    {
        $this->selectedPriority = $priority;
        $colors = $this->priorityColors[$priority] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
        
        // Handle both old string format and new array format
        if (is_string($colors)) {
            $this->selectedBgColor = $colors;
            $this->selectedTextColor = $this->ticketColorService->getContrastColor($colors);
        } else {
            $this->selectedBgColor = $colors['bg'] ?? '#f3f4f6';
            $this->selectedTextColor = $colors['text'] ?? '#374151';
        }
        
        $this->customBgColor = $this->selectedBgColor;
        $this->customTextColor = $this->selectedTextColor;
        $this->showColorPicker = true;
    }

    public function updatePriorityColor()
    {
        if (!$this->selectedPriority) {
            return;
        }

        try {
            $this->priorityColors[$this->selectedPriority] = [
                'bg' => $this->selectedBgColor,
                'text' => $this->selectedTextColor
            ];
            $this->ticketColorService->updatePriorityColors($this->priorityColors);
            
            $this->showFlashMessage("Colors updated for {$this->selectedPriority} priority!", 'success');
            $this->showColorPicker = false;
        } catch (\Exception $e) {
            $this->showFlashMessage('Error updating priority colors: ' . $e->getMessage(), 'error');
        }
    }

    public function resetToDefaults()
    {
        try {
            $this->ticketColorService->resetPriorityColorsToDefaults();
            $this->loadPriorityColors();
            $this->showFlashMessage('Priority colors reset to defaults!', 'success');
        } catch (\Exception $e) {
            $this->showFlashMessage('Error resetting colors: ' . $e->getMessage(), 'error');
        }
    }

    public function getPriorityOptions()
    {
        return TicketPriority::options();
    }

    public function getColorPalette()
    {
        return $this->ticketColorService->getColorPalette();
    }

    public function setCustomBgColor($color)
    {
        $this->customBgColor = $color;
        $this->selectedBgColor = $color;
    }

    public function setCustomTextColor($color)
    {
        $this->customTextColor = $color;
        $this->selectedTextColor = $color;
    }

    public function getColorDetails($colorName)
    {
        return $this->ticketColorService->getColorDetails($colorName);
    }

    public function getPreviewClasses($colorName)
    {
        return $this->ticketColorService->getPreviewClasses($colorName);
    }

    private function showFlashMessage($message, $type = 'success')
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
        $this->showFlash = true;

        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        return view('livewire.settings.tickets.priority');
    }
}
