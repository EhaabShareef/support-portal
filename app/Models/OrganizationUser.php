<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationUser extends Model
{
    protected $table = 'organization_users';
    
    protected $fillable = [
        'user_id',
        'organization_id',
        'is_primary'
    ];
    
    protected $casts = [
        'is_primary' => 'boolean'
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    
    // Validation rules
    public static function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'is_primary' => 'boolean'
        ];
    }
    
    // Business logic
    public static function setPrimaryUser($userId, $organizationId)
    {
        // Remove existing primary user for this organization
        self::where('organization_id', $organizationId)
             ->where('is_primary', true)
             ->update(['is_primary' => false]);
        
        // Set new primary user
        self::where('user_id', $userId)
             ->where('organization_id', $organizationId)
             ->update(['is_primary' => true]);
        
        // Update organization table
        Organization::where('id', $organizationId)
                   ->update(['primary_user_id' => $userId]);
    }
}
