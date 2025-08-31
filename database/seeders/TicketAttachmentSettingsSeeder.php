<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class TicketAttachmentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // File size settings
            [
                'key' => 'tickets.attachments.max_file_size',
                'value' => '10',
                'type' => 'float',
                'group' => 'tickets',
                'label' => 'Maximum File Size',
                'description' => 'Maximum file size for uploads',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.max_file_size_unit',
                'value' => 'MB',
                'type' => 'string',
                'group' => 'tickets',
                'label' => 'Maximum File Size Unit',
                'description' => 'Unit for maximum file size (KB, MB, GB)',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // File count settings
            [
                'key' => 'tickets.attachments.max_files_per_ticket',
                'value' => '5',
                'type' => 'integer',
                'group' => 'tickets',
                'label' => 'Maximum Files per Ticket',
                'description' => 'Maximum number of files allowed per ticket',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.max_files_per_message',
                'value' => '3',
                'type' => 'integer',
                'group' => 'tickets',
                'label' => 'Maximum Files per Message',
                'description' => 'Maximum number of files allowed per message',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // File type settings
            [
                'key' => 'tickets.attachments.allowed_file_types',
                'value' => json_encode([
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                    'application/zip',
                    'application/x-rar-compressed'
                ]),
                'type' => 'array',
                'group' => 'tickets',
                'label' => 'Allowed File Types',
                'description' => 'File types that are allowed for upload',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.blocked_file_types',
                'value' => json_encode([
                    'application/x-executable',
                    'application/x-msdownload',
                    'application/x-msi',
                    'application/x-shockwave-flash',
                    'application/x-javascript'
                ]),
                'type' => 'array',
                'group' => 'tickets',
                'label' => 'Blocked File Types',
                'description' => 'File types that are blocked from upload',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Security settings
            [
                'key' => 'tickets.attachments.scan_for_viruses',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Scan for Viruses',
                'description' => 'Enable virus scanning for uploaded files',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.require_file_scan',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Require File Scan',
                'description' => 'Block files until scan completes',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Storage settings
            [
                'key' => 'tickets.attachments.storage_location',
                'value' => 'local',
                'type' => 'string',
                'group' => 'tickets',
                'label' => 'Storage Location',
                'description' => 'Where to store uploaded files',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.auto_compress_images',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Auto-compress Images',
                'description' => 'Automatically compress uploaded images',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.image_quality',
                'value' => '85',
                'type' => 'integer',
                'group' => 'tickets',
                'label' => 'Image Quality',
                'description' => 'Quality setting for image compression (1-100)',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // UI settings
            [
                'key' => 'tickets.attachments.show_file_preview',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Show File Preview',
                'description' => 'Display file previews before upload',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.enable_drag_drop',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Enable Drag & Drop',
                'description' => 'Allow drag and drop file uploads',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'tickets.attachments.show_upload_progress',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Show Upload Progress',
                'description' => 'Display upload progress indicators',
                'is_public' => false,
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
