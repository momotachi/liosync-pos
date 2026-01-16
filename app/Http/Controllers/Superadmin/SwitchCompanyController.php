<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwitchCompanyController extends Controller
{
    protected $companyContext;

    public function __construct(CompanyContextService $companyContext)
    {
        $this->companyContext = $companyContext;
    }

    /**
     * Switch to a different company.
     */
    public function switch(Request $request, Company $company)
    {
        // Verify user is superadmin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        // Set company context
        $this->companyContext->setCompanyId($company->id);
        $this->companyContext->setBranchId(null);

        return redirect()->back()->with('success', "Switched to {$company->name}");
    }

    /**
     * Enter company admin (for superadmin to access company admin panel).
     * Goes to company dashboard for multi-branch companies.
     * For single company, enters directly to branch admin panel.
     */
    public function enterCompany(Request $request, Company $company)
    {
        // Verify user is superadmin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        // Set company context
        $this->companyContext->setCompanyId($company->id);

        // Check if company is multi-branch or single
        if ($company->has_branches) {
            // Multi-branch: go to company dashboard
            return redirect()->route('company.dashboard', $company)
                ->with('success', "Entered {$company->name} dashboard");
        }

        // Single company: enter the default branch directly
        $firstBranch = $company->branches()->first();

        if ($firstBranch) {
            // Enter the main branch
            $this->companyContext->setBranchId($firstBranch->id);
            return redirect('/branch')->with('success', "Entered {$company->name} - {$firstBranch->name}");
        }

        // No branch exists yet
        return redirect('/branch')->with('success', "Entered {$company->name}");
    }

    /**
     * Switch to a specific branch (enter branch admin).
     * Superadmin can enter any branch.
     * Company admin can enter branches belonging to their company.
     * Branch/Stock admin can enter their assigned branch.
     */
    public function switchBranch(Request $request, Branch $branch)
    {
        // Verify user has permission to access this branch
        /** @var User $user */
        $user = Auth::user();

        $canAccess = false;

        if ($user->isSuperAdmin()) {
            // Superadmin can access any branch
            $canAccess = true;
        } elseif ($user->isCompanyAdmin()) {
            // Company admin can access branches in their company
            $canAccess = $user->company_id === $branch->company_id;
        } elseif ($user->isBranchAdmin() || $user->isStockAdmin()) {
            // Branch/Stock admin can access their assigned branch
            $canAccess = $user->branch_id === $branch->id;
        }

        if (!$canAccess) {
            abort(403, 'Access denied');
        }

        // Set company and branch context
        $this->companyContext->setCompanyId($branch->company_id);
        $this->companyContext->setBranchId($branch->id);

        return redirect('/branch')->with('success', "Entered branch: {$branch->name}");
    }

    /**
     * Clear company context (view all companies).
     */
    public function clear(Request $request)
    {
        // Verify user is superadmin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        $this->companyContext->clearContext();

        return redirect()->back()->with('success', 'Viewing all companies');
    }
}
