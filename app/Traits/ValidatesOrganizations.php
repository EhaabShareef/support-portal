<?php

namespace App\Traits;

trait ValidatesOrganizations
{
    /**
     * Get the base validation rules for organization forms.
     *
     * @return array
     */
    protected function getOrganizationValidationRules(): array
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.company' => 'nullable|string|max:255',
            'form.company_contact' => 'required|string|max:255',
            'form.tin_no' => 'nullable|string|max:255|unique:organizations,tin_no',
            'form.is_active' => 'boolean',
            'form.subscription_status' => 'required|in:trial,active,suspended,cancelled',
            'form.notes' => 'nullable|string',
            'form.primary_user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get validation rules for organization forms, optionally excluding an ID from unique constraints.
     *
     * @param int|null $excludeId The organization ID to exclude from unique validation
     * @return array
     */
    protected function getOrganizationValidationRulesWithExclusion(?int $excludeId = null): array
    {
        $rules = $this->getOrganizationValidationRules();
        
        if ($excludeId) {
            $rules['form.tin_no'] = 'nullable|string|max:255|unique:organizations,tin_no,' . $excludeId;
        }
        
        return $rules;
    }

    /**
     * Get validation messages for organization forms.
     *
     * @return array
     */
    protected function getOrganizationValidationMessages(): array
    {
        return [
            'form.name.required' => 'Organization name is required',
            'form.company_contact.required' => 'Company contact is required',
            'form.tin_no.unique' => 'This TIN number is already in use',
            'form.primary_user_id.exists' => 'Selected user does not exist',
            'form.subscription_status.required' => 'Subscription status is required',
            'form.subscription_status.in' => 'Invalid subscription status selected',
        ];
    }
}