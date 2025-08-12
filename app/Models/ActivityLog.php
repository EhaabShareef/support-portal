<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id', 
        'event',
        'description',
        'user_id',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'batch_uuid'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array', 
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get activities for a specific subject.
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_type', get_class($subject))
                    ->where('subject_id', $subject->getKey());
    }

    /**
     * Scope to get activities by event type.
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to get activities by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get activities in a batch.
     */
    public function scopeInBatch($query, string $batchUuid)
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    /**
     * Get changes made in this activity.
     */
    public function getChanges(): array
    {
        if ($this->event === 'updated' && $this->old_values && $this->new_values) {
            return array_diff_assoc($this->new_values, $this->old_values);
        }

        return [];
    }

    /**
     * Check if a specific attribute was changed.
     */
    public function wasAttributeChanged(string $attribute): bool
    {
        $changes = $this->getChanges();
        return array_key_exists($attribute, $changes);
    }

    /**
     * Get the old value of an attribute.
     */
    public function getOldValue(string $attribute)
    {
        return $this->old_values[$attribute] ?? null;
    }

    /**
     * Get the new value of an attribute.
     */
    public function getNewValue(string $attribute)
    {
        return $this->new_values[$attribute] ?? null;
    }

    /**
     * Static helper to record an activity.
     */
    public static function record(string $event, $subjectId, $subject = null, array $properties = []): self
    {
        $user = auth()->user();
        
        return self::create([
            'event' => $event,
            'subject_type' => $subject ? get_class($subject) : 'App\Models\Ticket',
            'subject_id' => $subjectId,
            'user_id' => $user?->id,
            'description' => $properties['description'] ?? null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}