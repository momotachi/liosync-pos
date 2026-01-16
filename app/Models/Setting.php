<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    protected $fillable = [
        'branch_id',
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get the branch that owns the setting.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the effective branch ID for settings.
     * Returns current session branch_id or user's branch_id.
     */
    private static function getEffectiveBranchId()
    {
        // Check for active branch context (Superadmin/Company Admin viewing branch)
        if (session('active_branch_id')) {
            return session('active_branch_id');
        }

        // Use authenticated user's branch
        $user = Auth::user();
        return $user ? $user->branch_id : null;
    }

    /**
     * Get a setting value by key for the current branch.
     */
    public static function get(string $key, $default = null)
    {
        $branchId = self::getEffectiveBranchId();

        $setting = static::where('key', $key)
            ->where('branch_id', $branchId)
            ->first();

        if (!$setting) {
            return $default;
        }

        // Return value based on type
        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'number' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value for the current branch.
     */
    public static function set(string $key, $value): void
    {
        $branchId = self::getEffectiveBranchId();

        $setting = static::where('key', $key)
            ->where('branch_id', $branchId)
            ->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        }
    }

    /**
     * Scope to get settings by group.
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group)->orderBy('label');
    }

    /**
     * Get all settings for the current branch grouped by their group.
     */
    public static function grouped()
    {
        $branchId = self::getEffectiveBranchId();

        return static::where('branch_id', $branchId)
            ->get()
            ->groupBy('group');
    }

    /**
     * Scope to get settings for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
