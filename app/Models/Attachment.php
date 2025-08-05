<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'attachable_type',
        'attachable_id',
        'original_name',
        'stored_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'extension',
        'is_public',
        'is_image',
        'metadata',
        'uploaded_by',
        'download_count',
        'last_downloaded_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_image' => 'boolean',
        'metadata' => 'array',
        'size' => 'integer',
        'download_count' => 'integer',
        'last_downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = Str::uuid();
            }
        });

        static::deleting(function ($attachment) {
            // Delete the actual file when the model is deleted
            if ($attachment->path && Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }
        });
    }

    // Polymorphic relationship
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    // User who uploaded the file
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper methods
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('attachments.download', $this->uuid);
    }

    public function canBeViewedInBrowser(): bool
    {
        $viewableTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
        ];

        return in_array($this->mime_type, $viewableTypes);
    }

    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return 'heroicon-o-photo';
        }

        $iconMap = [
            'application/pdf' => 'heroicon-o-document-text',
            'application/msword' => 'heroicon-o-document-text',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'heroicon-o-document-text',
            'application/vnd.ms-excel' => 'heroicon-o-table-cells',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'heroicon-o-table-cells',
            'application/zip' => 'heroicon-o-archive-box',
            'application/x-rar-compressed' => 'heroicon-o-archive-box',
        ];

        return $iconMap[$this->mime_type] ?? 'heroicon-o-document';
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    // Scope for public attachments
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // Scope for private attachments
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    // Scope for images
    public function scopeImages($query)
    {
        return $query->where('is_image', true);
    }
}