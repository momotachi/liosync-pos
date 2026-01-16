<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class SetRestoCompanyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all companies to have 'resto' type for testing
        $updated = Company::query()->update(['type' => 'resto']);

        $this->command->info("Updated {$updated} companies to 'resto' type.");
    }
}
