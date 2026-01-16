<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompanyContextService
{
    /**
     * Get the current company ID from session.
     */
    public function getCompanyId(): ?int
    {
        return Session::get('company_id');
    }

    /**
     * Get the current branch ID from session.
     */
    public function getBranchId(): ?int
    {
        return Session::get('branch_id');
    }

    /**
     * Set the current company context (for superadmin).
     */
    public function setCompanyId(?int $companyId): void
    {
        if ($companyId === null) {
            Session::forget('company_id');
        } else {
            Session::put('company_id', $companyId);
        }
    }

    /**
     * Set the current branch context (for superadmin/company admin).
     */
    public function setBranchId(?int $branchId): void
    {
        if ($branchId === null) {
            Session::forget('branch_id');
        } else {
            Session::put('branch_id', $branchId);
        }
    }

    /**
     * Check if user can switch to a different company.
     */
    public function canSwitchCompany(): bool
    {
        return Auth::check() && Auth::user()->isSuperAdmin();
    }

    /**
     * Check if user can switch to a different branch.
     */
    public function canSwitchBranch(): bool
    {
        return Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin());
    }

    /**
     * Clear company context (when switching to all companies view).
     */
    public function clearContext(): void
    {
        Session::forget('company_id');
        Session::forget('branch_id');
    }

    /**
     * Get the company context as an array.
     */
    public function getContext(): array
    {
        return [
            'company_id' => $this->getCompanyId(),
            'branch_id' => $this->getBranchId(),
            'can_switch_company' => $this->canSwitchCompany(),
            'can_switch_branch' => $this->canSwitchBranch(),
        ];
    }
}
