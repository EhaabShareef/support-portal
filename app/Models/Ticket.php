<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property string $uuid
 * @property string $ticket_number
 * @property string $subject
 * @property string $type
 * @property string $status
 * @property string $priority
 * @property string|null $description
 * @property int $organization_id
 * @property int $client_id
 * @property int $department_id
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $first_response_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int|null $response_time_minutes
 * @property int|null $resolution_time_minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\User $client
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\User|null $assigned
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketMessage> $messages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketNote> $notes
 */
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'ticket_number',
        'subject',
        'type',
        'status',
        'priority',
        'description',
        'organization_id',
        'client_id',
        'department_id',
        'assigned_to',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'response_time_minutes',
        'resolution_time_minutes',
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    // Organization
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    // Client (User who created the ticket)
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Department
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Assigned User
    public function assigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Messages
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    // Notes
    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    // Attachments
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Priority enum
    public function getPriorityEnum(): ?TicketPriority
    {
        return TicketPriority::tryFrom($this->priority);
    }

    // Status enum
    public function getStatusEnum(): ?TicketStatus
    {
        return TicketStatus::tryFrom($this->status);
    }

    // Get priority metadata for display
    public function getPriorityMeta(): array
    {
        $priority = $this->getPriorityEnum();

        if (! $priority) {
            return [
                'icon' => 'heroicon-o-arrow-path',
                'class' => 'text-sky-700 dark:text-sky-500',
                'text' => 'NORMAL',
            ];
        }

        return [
            'icon' => $priority->icon(),
            'class' => $priority->cssClass(),
            'text' => $priority->displayText(),
        ];
    }

    // Get status CSS class
    public function getStatusCssClass(): string
    {
        $status = $this->getStatusEnum();

        return $status
            ? $status->cssClass()
            : 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300';
    }

    // Generate unique ticket number
    public static function generateTicketNumber(): string
    {
        $prefix = 'TK';
        $year = date('Y');
        $month = date('m');

        // Get the latest ticket number for current month
        $latest = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($latest && $latest->ticket_number) {
            // Extract sequence from ticket number (format: TK-YYYYMM-NNNN)
            $parts = explode('-', $latest->ticket_number);
            if (count($parts) === 3) {
                $sequence = intval($parts[2]) + 1;
            }
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', '!=', 'closed');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Helper methods
    public function isOpen(): bool
    {
        return $this->status !== 'closed';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isAssigned(): bool
    {
        return ! is_null($this->assigned_to);
    }

    public function getResponsesCountAttribute(): int
    {
        return $this->messages()->count();
    }

    public function getCategoryAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        $status = TicketStatus::tryFrom($this->status);

        return $status ? $status->label() : ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityLabelAttribute(): string
    {
        $priority = TicketPriority::tryFrom($this->priority);

        return $priority ? $priority->label() : ucfirst($this->priority);
    }
}
