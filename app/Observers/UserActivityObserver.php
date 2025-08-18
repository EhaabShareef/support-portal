<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserActivityObserver
{
    protected ActivityLogger $logger;

    protected array $typeMap = [
        \App\Models\Ticket::class => 'tickets',
        \App\Models\Organization::class => 'organizations',
        \App\Models\OrganizationContract::class => 'contracts',
        \App\Models\OrganizationHardware::class => 'hardware',
        \App\Models\Schedule::class => 'schedules',
        \App\Models\User::class => 'users',
        \Spatie\Permission\Models\Role::class => 'roles',
        \Spatie\Permission\Models\Permission::class => 'permissions',
        \App\Models\Setting::class => 'settings',
    ];

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    protected function type(Model $model): string
    {
        return $this->typeMap[get_class($model)] ?? Str::plural(class_basename($model));
    }

    public function created(Model $model): void
    {
        $this->logger->logModelChange(auth()->user(), $this->type($model), 'created', $model, 'Created');
    }

    public function updated(Model $model): void
    {
        $action = $model->wasChanged('status') || $model->wasChanged('status_id')
            ? 'status_changed'
            : 'updated';
        $this->logger->logModelChange(auth()->user(), $this->type($model), $action, $model, Str::ucfirst(str_replace('_', ' ', $action)));
    }

    public function deleted(Model $model): void
    {
        $this->logger->logModelChange(auth()->user(), $this->type($model), 'deleted', $model, 'Deleted');
    }
}
