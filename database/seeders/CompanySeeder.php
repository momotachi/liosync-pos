<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo companies
        $company1 = Company::firstOrCreate(
            ['code' => 'JUICE001'],
            [
                'name' => 'JuicePOS Demo Company',
                'slug' => 'juicepos-demo',
                'address' => 'Jl. Demo Street No. 123, Jakarta',
                'phone' => '021-12345678',
                'email' => 'demo@juicepos.com',
                'tax_id' => '01.234.567.8-901.000',
                'is_active' => true,
                'has_branches' => true, // Multi-branch company
            ]
        );

        // Create branches for company 1
        Branch::firstOrCreate(
            ['company_id' => $company1->id, 'code' => 'JKT01'],
            [
                'name' => 'Jakarta Branch',
                'address' => 'Jl. Jakarta No. 1',
                'phone' => '021-111111',
                'email' => 'jakarta@juicepos.com',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['company_id' => $company1->id, 'code' => 'BDG01'],
            [
                'name' => 'Bandung Branch',
                'address' => 'Jl. Bandung No. 1',
                'phone' => '022-222222',
                'email' => 'bandung@juicepos.com',
                'is_active' => true,
            ]
        );

        // Create second company (single company, no branches)
        $company2 = Company::firstOrCreate(
            ['code' => 'FRUIT002'],
            [
                'name' => 'Fresh Fruit Store',
                'slug' => 'fresh-fruit-store',
                'address' => 'Jl. Fruit Street No. 456, Surabaya',
                'phone' => '031-87654321',
                'email' => 'info@freshfruit.com',
                'tax_id' => '02.345.678.9-012.000',
                'is_active' => true,
                'has_branches' => false, // Single company (will have default branch created)
            ]
        );

        // Create default branch for single company
        Branch::firstOrCreate(
            ['company_id' => $company2->id, 'code' => 'FRUIT002_MAIN'],
            [
                'name' => 'Main Branch',
                'address' => 'Jl. Fruit Street No. 456, Surabaya',
                'phone' => '031-87654321',
                'email' => 'info@freshfruit.com',
                'is_active' => true,
            ]
        );

        $this->command->info('Companies and branches seeded successfully.');
    }
}
