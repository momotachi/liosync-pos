<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'address',
        'phone',
        'email',
        'type',
        'logo',
        'tax_id',
        'is_active',
        'has_branches',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_branches' => 'boolean',
    ];

    /**
     * Get the branches for the company.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the items for the company.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the settings for the company.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * Get the orders for the company.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the purchases for the company.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope to get only active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get company by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the company's logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-company-logo.png');
    }
}
