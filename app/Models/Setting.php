<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'company_id',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get the company that owns the setting.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get a setting value by key (respects company context)
     */
    public static function get(string $key, $default = null)
    {
        $companyId = Session::get('company_id');

        $query = static::where('key', $key);

        // First try to get company-specific setting
        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->whereNull('company_id');
        }

        $setting = $query->first();

        // If no company-specific setting found, fall back to global setting
        if (!$setting && $companyId) {
            $setting = static::where('key', $key)->whereNull('company_id')->first();
        }

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($setting->value) ? floatval($setting->value) : $default,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key (respects company context)
     */
    public static function set(string $key, $value): void
    {
        $companyId = Session::get('company_id');

        $query = static::where('key', $key);

        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->whereNull('company_id');
        }

        $setting = $query->first();

        if (!$setting) {
            // Try to find a global setting to use as template
            $globalSetting = static::where('key', $key)->whereNull('company_id')->first();

            if ($globalSetting && $companyId) {
                // Create company-specific setting based on global
                $setting = $globalSetting->replicate();
                $setting->company_id = $companyId;
            } else {
                return;
            }
        }

        $setting->value = match ($setting->type) {
            'boolean' => $value ? '1' : '0',
            'number' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };

        $setting->save();
    }

    /**
     * Get all settings grouped by group (respects company context)
     */
    public static function grouped(): array
    {
        $companyId = Session::get('company_id');

        $query = static::query();

        if ($companyId) {
            // Get company-specific settings, fall back to global for missing ones
            $companySettings = static::where('company_id', $companyId)->get()->keyBy('key');
            $globalSettings = static::whereNull('company_id')->get()->keyBy('key');

            $settings = $globalSettings->map(function ($globalSetting) use ($companySettings) {
                return $companySettings->get($globalSetting->key, $globalSetting);
            });
        } else {
            $settings = static::all();
        }

        $settings = $settings->groupBy('group');

        $groupLabels = [
            'general' => 'General Settings',
            'pos' => 'POS Settings',
            'receipt' => 'Receipt Settings',
            'tax' => 'Tax Settings',
            'inventory' => 'Inventory Settings',
        ];

        $orderedGroups = [];
        foreach ($groupLabels as $group => $label) {
            if ($settings->has($group)) {
                $orderedGroups[$group] = [
                    'label' => $label,
                    'settings' => $settings->get($group),
                ];
            }
        }

        // Add any groups not defined
        foreach ($settings as $group => $groupSettings) {
            if (!isset($orderedGroups[$group])) {
                $orderedGroups[$group] = [
                    'label' => ucfirst($group) . ' Settings',
                    'settings' => $groupSettings,
                ];
            }
        }

        return $orderedGroups;
    }

    /**
     * Scope to get settings by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get settings by company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get global settings (no company)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }
}
