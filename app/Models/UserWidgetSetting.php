<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWidgetSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'widget_id',
        'visible',
        'size',
        'sort_order',
        'options',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'sort_order' => 'integer',
        'options' => 'array',
    ];

    /**
     * Get the user that owns the widget setting
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the dashboard widget this setting belongs to
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class, 'widget_id');
    }

    /**
     * Scope to get visible widgets
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get effective size (validates against widget's available sizes)
     */
    public function getEffectiveSize(): string
    {
        if (!$this->widget) {
            return $this->size ?? '2x2';
        }

        // Ensure selected size is available for this widget
        if (in_array($this->size, $this->widget->available_sizes)) {
            return $this->size;
        }
        
        return $this->widget->default_size;
    }

    /**
     * Get effective order (use setting or widget default)
     */
    public function getEffectiveOrder(): int
    {
        return $this->sort_order ?? $this->widget->default_order ?? 0;
    }

    /**
     * Get merged options (widget default + user overrides)
     */
    public function getMergedOptions(): array
    {
        $defaultOptions = $this->widget->default_options ?? [];
        $userOptions = $this->options ?? [];

        return array_merge($defaultOptions, $userOptions);
    }

    /**
     * Get the component name for the current size
     */
    public function getComponentName(): string
    {
        return $this->widget->getComponentForSize($this->getEffectiveSize());
    }
}