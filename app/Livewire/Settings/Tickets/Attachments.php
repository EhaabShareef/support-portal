<?php

namespace App\Livewire\Settings\Tickets;

use Livewire\Component;
use App\Models\Setting;

class Attachments extends Component
{
    // File size settings
    public $maxFileSize = 10; // MB
    public $maxFileSizeUnit = 'MB';
    
    // File count settings
    public $maxFilesPerTicket = 5;
    public $maxFilesPerMessage = 3;
    
    // File type settings
    public $allowedFileTypes = [];
    public $blockedFileTypes = [];
    
    // Security settings
    public $scanForViruses = false;
    public $requireFileScan = false;
    
    // Storage settings
    public $storageLocation = 'local';
    public $autoCompressImages = true;
    public $imageQuality = 85;
    
    // UI settings
    public $showFilePreview = true;
    public $enableDragDrop = true;
    public $showUploadProgress = true;

    // Available options
    public $fileSizeUnits = ['KB', 'MB', 'GB'];
    public $storageOptions = [
        'local' => 'Local Storage',
        's3' => 'Amazon S3',
        'gcs' => 'Google Cloud Storage'
    ];
    
    public $defaultAllowedTypes = [
        'image/jpeg' => 'JPEG Images',
        'image/png' => 'PNG Images',
        'image/gif' => 'GIF Images',
        'image/webp' => 'WebP Images',
        'application/pdf' => 'PDF Documents',
        'application/msword' => 'Word Documents (.doc)',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word Documents (.docx)',
        'application/vnd.ms-excel' => 'Excel Files (.xls)',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel Files (.xlsx)',
        'text/plain' => 'Text Files',
        'application/zip' => 'ZIP Archives',
        'application/x-rar-compressed' => 'RAR Archives'
    ];
    
    public $defaultBlockedTypes = [
        'application/x-executable' => 'Executable Files',
        'application/x-msdownload' => 'Windows Executables',
        'application/x-msi' => 'Windows Installers',
        'application/x-shockwave-flash' => 'Flash Files',
        'application/x-javascript' => 'JavaScript Files'
    ];

    // UI state
    public $showFlash = false;
    public $flashMessage = '';
    public $flashType = 'success';

    protected $rules = [
        'maxFileSize' => 'required|numeric|min:0.1|max:1000',
        'maxFileSizeUnit' => 'required|in:KB,MB,GB',
        'maxFilesPerTicket' => 'required|integer|min:1|max:50',
        'maxFilesPerMessage' => 'required|integer|min:1|max:20',
        'allowedFileTypes' => 'array',
        'blockedFileTypes' => 'array',
        'scanForViruses' => 'boolean',
        'requireFileScan' => 'boolean',
        'storageLocation' => 'required|in:local,s3,gcs',
        'autoCompressImages' => 'boolean',
        'imageQuality' => 'required_if:autoCompressImages,true|integer|min:10|max:100',
        'showFilePreview' => 'boolean',
        'enableDragDrop' => 'boolean',
        'showUploadProgress' => 'boolean',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Load existing settings
        $this->maxFileSize = Setting::get('tickets.attachments.max_file_size', 10);
        $this->maxFileSizeUnit = Setting::get('tickets.attachments.max_file_size_unit', 'MB');
        $this->maxFilesPerTicket = Setting::get('tickets.attachments.max_files_per_ticket', 5);
        $this->maxFilesPerMessage = Setting::get('tickets.attachments.max_files_per_message', 3);
        $this->allowedFileTypes = Setting::get('tickets.attachments.allowed_file_types', array_keys($this->defaultAllowedTypes));
        $this->blockedFileTypes = Setting::get('tickets.attachments.blocked_file_types', array_keys($this->defaultBlockedTypes));
        $this->scanForViruses = Setting::get('tickets.attachments.scan_for_viruses', false);
        $this->requireFileScan = Setting::get('tickets.attachments.require_file_scan', false);
        $this->storageLocation = Setting::get('tickets.attachments.storage_location', 'local');
        $this->autoCompressImages = Setting::get('tickets.attachments.auto_compress_images', true);
        $this->imageQuality = Setting::get('tickets.attachments.image_quality', 85);
        $this->showFilePreview = Setting::get('tickets.attachments.show_file_preview', true);
        $this->enableDragDrop = Setting::get('tickets.attachments.enable_drag_drop', true);
        $this->showUploadProgress = Setting::get('tickets.attachments.show_upload_progress', true);
    }

    public function saveSettings()
    {
        $this->validate();

        try {
            // Save settings with proper types
            Setting::set('tickets.attachments.max_file_size', $this->maxFileSize, 'float', 'tickets');
            Setting::set('tickets.attachments.max_file_size_unit', $this->maxFileSizeUnit, 'string', 'tickets');
            Setting::set('tickets.attachments.max_files_per_ticket', $this->maxFilesPerTicket, 'integer', 'tickets');
            Setting::set('tickets.attachments.max_files_per_message', $this->maxFilesPerMessage, 'integer', 'tickets');
            Setting::set('tickets.attachments.allowed_file_types', $this->allowedFileTypes, 'array', 'tickets');
            Setting::set('tickets.attachments.blocked_file_types', $this->blockedFileTypes, 'array', 'tickets');
            Setting::set('tickets.attachments.scan_for_viruses', $this->scanForViruses, 'boolean', 'tickets');
            Setting::set('tickets.attachments.require_file_scan', $this->requireFileScan, 'boolean', 'tickets');
            Setting::set('tickets.attachments.storage_location', $this->storageLocation, 'string', 'tickets');
            Setting::set('tickets.attachments.auto_compress_images', $this->autoCompressImages, 'boolean', 'tickets');
            Setting::set('tickets.attachments.image_quality', $this->imageQuality, 'integer', 'tickets');
            Setting::set('tickets.attachments.show_file_preview', $this->showFilePreview, 'boolean', 'tickets');
            Setting::set('tickets.attachments.enable_drag_drop', $this->enableDragDrop, 'boolean', 'tickets');
            Setting::set('tickets.attachments.show_upload_progress', $this->showUploadProgress, 'boolean', 'tickets');

            $this->showFlashMessage('Attachment settings saved successfully!', 'success');
        } catch (\Exception $e) {
            $this->showFlashMessage('Error saving settings: ' . $e->getMessage(), 'error');
        }
    }

    public function resetToDefaults()
    {
        $this->maxFileSize = 10;
        $this->maxFileSizeUnit = 'MB';
        $this->maxFilesPerTicket = 5;
        $this->maxFilesPerMessage = 3;
        $this->allowedFileTypes = array_keys($this->defaultAllowedTypes);
        $this->blockedFileTypes = array_keys($this->defaultBlockedTypes);
        $this->scanForViruses = false;
        $this->requireFileScan = false;
        $this->storageLocation = 'local';
        $this->autoCompressImages = true;
        $this->imageQuality = 85;
        $this->showFilePreview = true;
        $this->enableDragDrop = true;
        $this->showUploadProgress = true;

        $this->showFlashMessage('Settings reset to defaults!', 'success');
    }

    public function toggleFileType($type, $list)
    {
        if ($list === 'allowed') {
            if (in_array($type, $this->allowedFileTypes)) {
                $this->allowedFileTypes = array_diff($this->allowedFileTypes, [$type]);
            } else {
                $this->allowedFileTypes[] = $type;
            }
        } else {
            if (in_array($type, $this->blockedFileTypes)) {
                $this->blockedFileTypes = array_diff($this->blockedFileTypes, [$type]);
            } else {
                $this->blockedFileTypes[] = $type;
            }
        }
    }

    public function getMaxFileSizeInBytes()
    {
        $multiplier = match($this->maxFileSizeUnit) {
            'KB' => 1024,
            'MB' => 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
            default => 1024 * 1024
        };
        
        return $this->maxFileSize * $multiplier;
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
        return view('livewire.settings.tickets.attachments');
    }
}
