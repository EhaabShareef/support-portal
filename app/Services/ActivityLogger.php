<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogger
{
    /**
     * Log a user activity event.
     */
    public function log($user, string $activityType, string $action, ?Model $target = null, string $message = null, array $changes = [], array $context = []): void
    {
        try {
            $snapshot = $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ] : null;

            $data = [
                'user_id' => $user?->id,
                'activity_type' => $activityType,
                'action' => $action,
                'model_type' => $target ? get_class($target) : null,
                'model_id' => $target?->getKey(),
                'message' => Str::limit($message ?? '', 255),
                'changes' => $changes ? $this->truncateChanges($changes) : null,
                'ip_address' => request()->attributes->get('ip_address'),
                'user_agent' => request()->attributes->get('user_agent'),
                'request_id' => request()->attributes->get('request_id'),
            ];

            if ($snapshot) {
                $data['changes']['actor'] = $snapshot;
            }

            if ($context) {
                $data['changes']['context'] = $context;
            }

            UserActivity::create($data);
        } catch (Throwable $e) {
            Log::warning('Failed to log user activity', [
                'error' => $e->getMessage(),
                'activity_type' => $activityType,
                'action' => $action,
            ]);
        }
    }

    /**
     * Log model changes by diffing fillable attributes.
     */
    public function logModelChange($user, string $activityType, string $action, Model $model, string $message = null, array $context = []): void
    {
        $fillable = $model->getFillable();
        $before = Arr::only($model->getOriginal(), $fillable);
        $after = Arr::only($model->getAttributes(), $fillable);
        $changes = ['before' => $before, 'after' => $after];
        $this->log($user, $activityType, $action, $model, $message, $changes, $context);
    }

    protected function truncateChanges(array $changes): array
    {
        $json = json_encode($changes);
        if (strlen($json) > 10000) {
            $changes = json_decode(substr($json, 0, 10000), true);
        }
        return $changes;
    }
}
