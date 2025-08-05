<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogsActivity
{
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $ignoreChangedAttributes = [];

    /**
     * Boot the trait.
     */
    protected static function bootLogsActivity(): void
    {
        foreach (static::$recordEvents as $event) {
            static::registerModelEvent($event, function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    /**
     * Log activity for the model.
     */
    public function logActivity(string $event): void
    {
        if (!$this->shouldLogActivity($event)) {
            return;
        }

        $properties = [];
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $oldValues = $this->getOriginalLoggableAttributes();
            $newValues = $this->getLoggableAttributes();
            
            // Only log if there are actual changes
            if (empty(array_diff_assoc($newValues, $oldValues))) {
                return;
            }
        } elseif ($event === 'created') {
            $newValues = $this->getLoggableAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getLoggableAttributes();
        }

        ActivityLog::create([
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'event' => $event,
            'description' => $this->getActivityDescription($event),
            'user_id' => Auth::id(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'properties' => $this->getActivityProperties($event),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'batch_uuid' => $this->getBatchUuid(),
        ]);
    }

    /**
     * Get loggable attributes.
     */
    protected function getLoggableAttributes(): array
    {
        if (in_array('*', static::$logAttributes)) {
            $attributes = $this->getAttributes();
        } else {
            $attributes = array_intersect_key(
                $this->getAttributes(),
                array_flip(static::$logAttributes)
            );
        }

        return array_diff_key($attributes, array_flip(static::$ignoreChangedAttributes));
    }

    /**
     * Get original loggable attributes.
     */
    protected function getOriginalLoggableAttributes(): array
    {
        if (in_array('*', static::$logAttributes)) {
            $attributes = $this->getOriginal();
        } else {
            $attributes = array_intersect_key(
                $this->getOriginal(),
                array_flip(static::$logAttributes)
            );
        }

        return array_diff_key($attributes, array_flip(static::$ignoreChangedAttributes));
    }

    /**
     * Get activity description.
     */
    protected function getActivityDescription(string $event): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getActivityIdentifier();

        return match($event) {
            'created' => "{$modelName} '{$identifier}' was created",
            'updated' => "{$modelName} '{$identifier}' was updated", 
            'deleted' => "{$modelName} '{$identifier}' was deleted",
            default => "{$modelName} '{$identifier}' was {$event}",
        };
    }

    /**
     * Get activity identifier (name, title, etc.).
     */
    protected function getActivityIdentifier(): string
    {
        return $this->name ?? $this->title ?? $this->getKey();
    }

    /**
     * Get additional activity properties.
     */
    protected function getActivityProperties(string $event): ?array
    {
        return null;
    }

    /**
     * Check if activity should be logged.
     */
    protected function shouldLogActivity(string $event): bool
    {
        return in_array($event, static::$recordEvents);
    }

    /**
     * Get batch UUID for grouping related activities.
     */
    protected function getBatchUuid(): ?string
    {
        return session('activity_batch_uuid');
    }

    /**
     * Start a new activity batch.
     */
    public static function startActivityBatch(): string
    {
        $batchUuid = Str::uuid();
        session(['activity_batch_uuid' => $batchUuid]);
        return $batchUuid;
    }

    /**
     * End the current activity batch.
     */
    public static function endActivityBatch(): void
    {
        session()->forget('activity_batch_uuid');
    }
}