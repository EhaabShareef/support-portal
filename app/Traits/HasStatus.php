<?php

namespace App\Traits;

trait HasStatus
{
    /**
     * Scope a query to only include active records.
     */
    public function scopeActive($query)
    {
        return $query->where($this->getStatusColumn(), $this->getActiveStatus());
    }

    /**
     * Scope a query to only include inactive records.
     */
    public function scopeInactive($query)
    {
        return $query->where($this->getStatusColumn(), '!=', $this->getActiveStatus());
    }

    /**
     * Check if the model is active.
     */
    public function isActive(): bool
    {
        return $this->{$this->getStatusColumn()} === $this->getActiveStatus();
    }

    /**
     * Mark the model as active.
     */
    public function activate(): bool
    {
        return $this->update([$this->getStatusColumn() => $this->getActiveStatus()]);
    }

    /**
     * Mark the model as inactive.
     */
    public function deactivate(): bool
    {
        return $this->update([$this->getStatusColumn() => $this->getInactiveStatus()]);
    }

    /**
     * Get the status column name.
     */
    protected function getStatusColumn(): string
    {
        return property_exists($this, 'statusColumn') ? $this->statusColumn : 'is_active';
    }

    /**
     * Get the active status value.
     */
    protected function getActiveStatus()
    {
        return property_exists($this, 'activeStatus') ? $this->activeStatus : true;
    }

    /**
     * Get the inactive status value.
     */
    protected function getInactiveStatus()
    {
        return property_exists($this, 'inactiveStatus') ? $this->inactiveStatus : false;
    }
}