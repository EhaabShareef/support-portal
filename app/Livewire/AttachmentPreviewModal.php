<?php

namespace App\Livewire;

use App\Models\TicketMessageAttachment;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class AttachmentPreviewModal extends Component
{
    public bool $show = false;
    public ?TicketMessageAttachment $attachment = null;
    private $lastOpenTime = 0;
    
    protected $listeners = ['open-attachment-preview' => 'openPreview'];

    public function openPreview($attachmentId)
    {
        $currentTime = microtime(true);
        
        // Prevent rapid successive calls (within 100ms)
        if ($currentTime - $this->lastOpenTime < 0.1) {
            return;
        }
        
        $this->lastOpenTime = $currentTime;

        // Prevent reopening if already open with same attachment
        if ($this->show && $this->attachment && $this->attachment->id == $attachmentId) {
            return;
        }

        $this->attachment = TicketMessageAttachment::find($attachmentId);
        
        if ($this->attachment) {
            // Check if file exists
            if (!Storage::disk($this->attachment->disk)->exists($this->attachment->path)) {
                session()->flash('error', 'File not found on server.');
                return;
            }
            
            $this->show = true;
        }
    }

    public function closePreview()
    {
        $this->show = false;
        $this->attachment = null;
    }

    public function download()
    {
        if (!$this->attachment) {
            return;
        }

        // Check if file exists
        if (!Storage::disk($this->attachment->disk)->exists($this->attachment->path)) {
            session()->flash('error', 'File not found on server.');
            return;
        }

        // Return file for download
        return Storage::disk($this->attachment->disk)->download(
            $this->attachment->path,
            $this->attachment->original_name,
            [
                'Content-Type' => $this->attachment->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $this->attachment->original_name . '"',
            ]
        );
    }

    public function getFileUrl()
    {
        if (!$this->attachment) {
            return null;
        }

        // For public disk, use direct URL
        if ($this->attachment->disk === 'public') {
            return Storage::disk('public')->url($this->attachment->path);
        }

        // For other disks, create a temporary signed URL for secure access
        try {
            return Storage::disk($this->attachment->disk)->temporaryUrl(
                $this->attachment->path,
                now()->addMinutes(5),
                [
                    'ResponseContentType' => $this->attachment->mime_type,
                    'ResponseContentDisposition' => 'inline; filename="' . $this->attachment->original_name . '"',
                ]
            );
        } catch (\Exception $e) {
            // Fallback to direct URL if temporary URL is not supported
            return Storage::disk($this->attachment->disk)->url($this->attachment->path);
        }
    }

    public function canPreview()
    {
        if (!$this->attachment) {
            return false;
        }

        $extension = strtolower(pathinfo($this->attachment->original_name, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $isPdf = $extension === 'pdf';
        
        return $isImage || $isPdf;
    }

    public function render()
    {
        return view('livewire.attachment-preview-modal');
    }
}
