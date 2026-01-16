<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'type',
        'amount',
        'note',
        'adjustment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'adjustment_date' => 'datetime',
    ];

    /**
     * Get the branch that owns the adjustment.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope to filter by type (cash or bank).
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter adjustments before a specific date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('adjustment_date', '<', $date);
    }

    /**
     * Scope to filter adjustments within a date range.
     */
    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('adjustment_date', [$from, $to]);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the sum of adjustments before a specific date.
     */
    public static function getTotalBeforeDate($type, $date, $branchId = null)
    {
        return static::ofType($type)
            ->beforeDate($date)
            ->when($branchId, function ($query) use ($branchId) {
                return $query->forBranch($branchId);
            })
            ->sum('amount');
    }
}
