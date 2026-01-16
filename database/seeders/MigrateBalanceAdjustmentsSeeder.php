<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BalanceAdjustment;
use App\Models\Setting;
use App\Models\Branch;

class MigrateBalanceAdjustmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();

        // Migrate default settings (no branch)
        $defaultCashAdjustment = (float) Setting::get('cash_balance_adjustment_default', 0);
        $defaultBankAdjustment = (float) Setting::get('bank_balance_adjustment_default', 0);

        if ($defaultCashAdjustment != 0 || $defaultBankAdjustment != 0) {
            // Only create if there's a value
            if ($defaultCashAdjustment != 0) {
                BalanceAdjustment::create([
                    'branch_id' => null,
                    'type' => 'cash',
                    'amount' => $defaultCashAdjustment,
                    'note' => 'Migrated from settings',
                    'adjustment_date' => now(),
                ]);
            }

            if ($defaultBankAdjustment != 0) {
                BalanceAdjustment::create([
                    'branch_id' => null,
                    'type' => 'bank',
                    'amount' => $defaultBankAdjustment,
                    'note' => 'Migrated from settings',
                    'adjustment_date' => now(),
                ]);
            }

            // Clear old settings
            Setting::forget('cash_balance_adjustment_default');
            Setting::forget('bank_balance_adjustment_default');
        }

        // Migrate branch-specific settings
        foreach ($branches as $branch) {
            $cashAdjustmentKey = 'cash_balance_adjustment_' . $branch->id;
            $bankAdjustmentKey = 'bank_balance_adjustment_' . $branch->id;

            $cashAdjustment = (float) Setting::get($cashAdjustmentKey, 0);
            $bankAdjustment = (float) Setting::get($bankAdjustmentKey, 0);

            if ($cashAdjustment != 0 || $bankAdjustment != 0) {
                // Only create if there's a value
                if ($cashAdjustment != 0) {
                    BalanceAdjustment::create([
                        'branch_id' => $branch->id,
                        'type' => 'cash',
                        'amount' => $cashAdjustment,
                        'note' => 'Migrated from settings',
                        'adjustment_date' => now(),
                    ]);
                }

                if ($bankAdjustment != 0) {
                    BalanceAdjustment::create([
                        'branch_id' => $branch->id,
                        'type' => 'bank',
                        'amount' => $bankAdjustment,
                        'note' => 'Migrated from settings',
                        'adjustment_date' => now(),
                    ]);
                }

                // Clear old settings
                Setting::forget($cashAdjustmentKey);
                Setting::forget($bankAdjustmentKey);
            }
        }

        $this->command->info('Balance adjustments migrated from settings successfully.');
    }
}
