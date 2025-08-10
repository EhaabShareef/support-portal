<?php

namespace App\Livewire\Dashboard\Widgets;

use Livewire\Component;

class FallbackWidget extends Component
{
    public string $widgetName;
    public string $widgetSize;

    public function mount(string $widgetName = 'Unknown Widget', string $widgetSize = '1x1'): void
    {
        $this->widgetName = $widgetName;
        $this->widgetSize = $widgetSize;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.fallback-widget');
    }
}