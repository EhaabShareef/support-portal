<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationContract;

class HardwareValidationService
{
    /**
     * Validate that hardware can be created/updated with the given contract.
     * 
     * @param Organization $organization
     * @param int|null $contractId
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validateHardwareContract(Organization $organization, $contractId = null): array
    {
        if ($contractId) {
            $contract = OrganizationContract::find($contractId);
            if (!$contract || !$contract->includes_hardware || $contract->status !== 'active') {
                return [
                    'valid' => false,
                    'error' => 'Hardware can only be assigned to active contracts that include hardware.'
                ];
            }
        } else {
            // Check if organization has any active hardware contracts
            $hasHardwareContract = $organization->contracts()
                ->where('status', 'active')
                ->where('includes_hardware', true)
                ->exists();
                
            if (!$hasHardwareContract) {
                return [
                    'valid' => false,
                    'error' => 'This organization must have an active hardware contract to add hardware. Please create a hardware contract first.'
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }
}