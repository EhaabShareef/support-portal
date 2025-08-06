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
            'form.company' => 'required|string|max:255',
            'form.company_contact' => 'required|string|max:255',
            'form.tin_no' => 'required|string|max:255|unique:organizations,tin_no',
            'form.email' => 'required|email|unique:organizations,email',
            'form.phone' => 'nullable|string|max:20',
            'form.is_active' => 'boolean',
            'form.subscription_status' => 'required|in:trial,active,suspended,cancelled',
            'form.notes' => 'nullable|string',
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
            $rules['form.tin_no'] = 'required|string|max:255|unique:organizations,tin_no,' . $excludeId;
            $rules['form.email'] = 'required|email|unique:organizations,email,' . $excludeId;
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
            'form.company.required' => 'Company name is required',
            'form.company_contact.required' => 'Company contact is required',
            'form.tin_no.required' => 'TIN number is required',
            'form.tin_no.unique' => 'This TIN number is already in use',
            'form.email.required' => 'Email is required',
            'form.email.email' => 'Please enter a valid email address',
            'form.email.unique' => 'This email is already in use',
            'form.phone.max' => 'Phone number cannot exceed 20 characters',
            'form.subscription_status.required' => 'Subscription status is required',
            'form.subscription_status.in' => 'Invalid subscription status selected',
        ];
    }
}