<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type_id',
        'start_date',
        'end_date',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(ScheduleEventType::class, 'event_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        return $query->where('start_date', '<=', $endOfMonth)
                    ->where('end_date', '>=', $startOfMonth);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
    }

    public function scopeForNewDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where('start_date', '<=', $endDate)
              ->where('end_date', '>=', $startDate);
        });
    }

    public function scopeOverlapsMonth($query, $year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        return $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('start_date', '<=', $endOfMonth)
              ->where('end_date', '>=', $startOfMonth);
        });
    }

    public function scopeWithEventType($query, $eventTypeId)
    {
        return $query->where('event_type_id', $eventTypeId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('user', function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    public function scopeForDepartmentGroup($query, $departmentGroupId)
    {
        return $query->whereHas('user.department', function ($q) use ($departmentGroupId) {
            $q->where('department_group_id', $departmentGroupId);
        });
    }

    // Accessors
    public function getFormattedDateAttribute(): string
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('M d, Y');
        }
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('M d, Y');
        }
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getDaysInMonthAttribute(): array
    {
        $days = [];
        $current = $this->start_date->copy();
        
        while ($current->lessThanOrEqualTo($this->end_date)) {
            $days[] = $current->day;
            $current->addDay();
        }
        
        return $days;
    }

    public function spansDay($day, $year = null, $month = null): bool
    {
        $year = $year ?? $this->start_date->year;
        $month = $month ?? $this->start_date->month;
        
        $checkDate = Carbon::create($year, $month, $day);
        
        return $checkDate->greaterThanOrEqualTo($this->start_date) && 
               $checkDate->lessThanOrEqualTo($this->end_date);
    }

    public function getDisplayBadgeAttribute(): string
    {
        $eventType = $this->eventType;
        $style = $eventType->color_style ? "style='{$eventType->color_style}'" : '';
        $class = $eventType->color_class !== 'custom-color' ? "bg-{$eventType->color_class}" : '';
        
        $title = $this->remarks ? "title='{$eventType->label}: {$this->remarks}'" : "title='{$eventType->label}'";
        
        return "<span class='inline-block px-1 rounded text-xs text-white {$class}' {$style} {$title}>{$eventType->code}</span>";
    }

    // Helper methods
    public static function getForCalendar($year, $month, $userIds = null, $departmentGroupId = null)
    {
        $query = static::with(['user:id,name,department_id', 'user.department:id,name,department_group_id', 'eventType:id,code,label,color'])
                       ->overlapsMonth($year, $month);

        if ($departmentGroupId) {
            $query->forDepartmentGroup($departmentGroupId);
        } elseif ($userIds) {
            $query->whereIn('user_id', $userIds);
        }

        return $query->get();
    }

    public static function getUsersWithSchedules($year, $month, $departmentGroupId = null)
    {
        $query = User::query()
            ->with(['department:id,name,department_group_id', 'department.departmentGroup:id,name'])
            ->whereHas('schedules', function ($q) use ($year, $month) {
                $q->forMonth($year, $month);
            });

        if ($departmentGroupId) {
            $query->whereHas('department', function ($q) use ($departmentGroupId) {
                $q->where('department_group_id', $departmentGroupId);
            });
        }

        return $query->orderBy('name')->get();
    }
}
