<?php

namespace App\Livewire\Admin\Settings\Modules\TicketSettings\Sections;

use App\Livewire\Admin\Settings\BaseSettingsComponent;

class AttachmentSection extends BaseSettingsComponent
{
    // Attachment Settings
    public int $attachmentMaxSizeMb = 10;
    public int $attachmentMaxCount = 5;
    public array $allowedFileTypes = [];
    public bool $enableImageCompression = true;
    public int $imageCompressionQuality = 80;
    public bool $scanForViruses = false;

    protected function getSettingsGroup(): string
    {
        return 'tickets.attachment';
    }

    protected function getTitle(): string
    {
        return 'Attachment Settings';
    }

    protected function getDescription(): string
    {
        return 'Configure file upload limits and settings';
    }

    protected function getIcon(): string
    {
        return 'heroicon-o-paper-clip';
    }

    protected function loadData(): void
    {
        $this->attachmentMaxSizeMb = (int) $this->getSetting('tickets.attachment.max_size_mb', 10);
        $this->attachmentMaxCount = (int) $this->getSetting('tickets.attachment.max_count', 5);
        $this->allowedFileTypes = $this->getSetting('tickets.attachment.allowed_types', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);
        $this->enableImageCompression = (bool) $this->getSetting('tickets.attachment.enable_image_compression', true);
        $this->imageCompressionQuality = (int) $this->getSetting('tickets.attachment.image_compression_quality', 80);
        $this->scanForViruses = (bool) $this->getSetting('tickets.attachment.scan_for_viruses', false);
    }

    protected function saveData(): void
    {
        $this->setSetting('tickets.attachment.max_size_mb', $this->attachmentMaxSizeMb, 'integer');
        $this->setSetting('tickets.attachment.max_count', $this->attachmentMaxCount, 'integer');
        $this->setSetting('tickets.attachment.allowed_types', $this->allowedFileTypes, 'json');
        $this->setSetting('tickets.attachment.enable_image_compression', $this->enableImageCompression, 'boolean');
        $this->setSetting('tickets.attachment.image_compression_quality', $this->imageCompressionQuality, 'integer');
        $this->setSetting('tickets.attachment.scan_for_viruses', $this->scanForViruses, 'boolean');
    }

    public function addFileType(string $fileType): void
    {
        $fileType = strtolower(trim($fileType));
        if (!empty($fileType) && !in_array($fileType, $this->allowedFileTypes)) {
            $this->allowedFileTypes[] = $fileType;
            $this->markAsChanged();
        }
    }

    public function removeFileType(int $index): void
    {
        if (isset($this->allowedFileTypes[$index])) {
            unset($this->allowedFileTypes[$index]);
            $this->allowedFileTypes = array_values($this->allowedFileTypes);
            $this->markAsChanged();
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.modules.ticket-settings.sections.attachment');
    }
}
