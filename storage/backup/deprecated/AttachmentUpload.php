<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentUpload extends Component
{
    use WithFileUploads;

    public $attachments = [];
    public $maxFileSize;
    public $allowedTypes;
    public $uploadProgress = [];
    public $dragOver = false;

    protected $listeners = ['clearAttachments' => 'clearAttachments'];

    public function mount()
    {
        $this->maxFileSize = config('app.max_file_size', 10240); // 10MB in KB
        $this->allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];
    }

    public function updatedAttachments()
    {
        $this->validate([
            'attachments.*' => [
                'file',
                'max:' . $this->maxFileSize,
                'mimes:' . implode(',', array_map(function($type) {
                    return str_replace('application/', '', str_replace('image/', '', $type));
                }, $this->allowedTypes))
            ]
        ]);

        // Update progress for each file
        foreach ($this->attachments as $index => $attachment) {
            $this->uploadProgress[$index] = 0;
        }
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            unset($this->uploadProgress[$index]);
            $this->attachments = array_values($this->attachments);
            $this->uploadProgress = array_values($this->uploadProgress);
        }
    }

    public function clearAttachments()
    {
        $this->attachments = [];
        $this->uploadProgress = [];
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function getTotalSize()
    {
        $total = 0;
        foreach ($this->attachments as $attachment) {
            $total += $attachment->getSize();
        }
        return $total;
    }

    public function getFormattedTotalSize()
    {
        $bytes = $this->getTotalSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function render()
    {
        return view('livewire.attachment-upload');
    }
}
