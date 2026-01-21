<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hint',
        'role',
        'is_active',
        'company_id',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the user.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the user.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if user is a superadmin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Check if user is a company admin.
     *
     * @return bool
     */
    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('Company Admin');
    }

    /**
     * Check if user is a branch admin.
     *
     * @return bool
     */
    public function isBranchAdmin(): bool
    {
        return $this->hasRole('Branch Admin');
    }

    /**
     * Check if user is a stock admin.
     *
     * @return bool
     */
    public function isStockAdmin(): bool
    {
        return $this->hasRole('Stock Admin');
    }

    /**
     * Check if user is a cashier.
     *
     * @return bool
     */
    public function isCashier(): bool
    {
        return $this->hasRole('Cashier');
    }

    /**
     * Check if user belongs to a specific company.
     *
     * @return bool
     */
    public function belongsToCompany(int $companyId): bool
    {
        return $this->company_id === $companyId || $this->isSuperAdmin();
    }

    /**
     * Check if user belongs to a specific branch.
     *
     * @return bool
     */
    public function belongsToBranch(int $branchId): bool
    {
        return $this->branch_id === $branchId || $this->isSuperAdmin() || $this->isCompanyAdmin();
    }

    /**
     * Check if user can access admin panel.
     *
     * @return bool
     */
    public function canAccessAdmin(): bool
    {
        return $this->is_active && (
            $this->isSuperAdmin() ||
            $this->isCompanyAdmin() ||
            $this->isBranchAdmin() ||
            $this->isStockAdmin()
        );
    }

    /**
     * Scope to get users for a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get users for a specific branch.
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get only superadmin users.
     */
    public function scopeSuperadminOnly($query)
    {
        return $query->role('Superadmin');
    }

    // Legacy methods for backward compatibility
    public function isAdmin(): bool
    {
        return $this->hasRole('Superadmin') || $this->hasRole('Company Admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Branch Admin');
    }
}
