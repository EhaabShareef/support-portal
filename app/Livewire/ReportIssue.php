<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ActivityLogger;

class ReportIssue extends Component
{
    public $show = false;
    public $message = '';

    protected $listeners = ['openReportIssue' => 'open'];

    public function open(): void
    {
        $this->show = true;
    }

    public function submit(): void
    {
        $context = [
            'route' => request()->path(),
            'request_id' => request()->attributes->get('request_id'),
            'last_error' => session('last_error'),
        ];

        app(ActivityLogger::class)->log(auth()->user(), 'issues', 'created', null, $this->message, [], $context);
        $this->reset('message');
        $this->show = false;
        $this->dispatch('issue-reported');
    }

    public function render()
    {
        return view('livewire.report-issue');
    }
}
