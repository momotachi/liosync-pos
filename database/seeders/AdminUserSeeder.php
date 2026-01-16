<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies and branches
        $company1 = Company::where('code', 'JUICE001')->first();
        $company2 = Company::where('code', 'FRUIT002')->first();

        $jakartaBranch = Branch::where('code', 'JKT01')->first();
        $bandungBranch = Branch::where('code', 'BDG01')->first();
        $surabayaBranch = Branch::where('code', 'SBY01')->first();

        // Create Superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@juicepos.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'company_id' => null,
                'branch_id' => null,
            ]
        );
        $superadmin->assignRole('Superadmin');

        // Create Company Admin for Company 1
        $companyAdmin1 = User::firstOrCreate(
            ['email' => 'admin@juicepos.com'],
            [
                'name' => 'Company Admin 1',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'company_id' => $company1->id,
                'branch_id' => null,
            ]
        );
        $companyAdmin1->assignRole('Company Admin');

        // Create Company Admin for Company 2
        $companyAdmin2 = User::firstOrCreate(
            ['email' => 'admin@freshfruit.com'],
            [
                'name' => 'Company Admin 2',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'company_id' => $company2->id,
                'branch_id' => null,
            ]
        );
        $companyAdmin2->assignRole('Company Admin');

        // Create Branch Admins
        $branchAdmin1 = User::firstOrCreate(
            ['email' => 'jakarta.admin@juicepos.com'],
            [
                'name' => 'Jakarta Branch Admin',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'company_id' => $company1->id,
                'branch_id' => $jakartaBranch->id,
            ]
        );
        $branchAdmin1->assignRole('Branch Admin');

        $branchAdmin2 = User::firstOrCreate(
            ['email' => 'bandung.admin@juicepos.com'],
            [
                'name' => 'Bandung Branch Admin',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'company_id' => $company1->id,
                'branch_id' => $bandungBranch->id,
            ]
        );
        $branchAdmin2->assignRole('Branch Admin');

        // Create Stock Admins
        $stockAdmin1 = User::firstOrCreate(
            ['email' => 'jakarta.stock@juicepos.com'],
            [
                'name' => 'Jakarta Stock Admin',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'company_id' => $company1->id,
                'branch_id' => $jakartaBranch->id,
            ]
        );
        $stockAdmin1->assignRole('Stock Admin');

        // Create Cashiers
        $cashier1 = User::firstOrCreate(
            ['email' => 'jakarta.cashier@juicepos.com'],
            [
                'name' => 'Jakarta Cashier 1',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'company_id' => $company1->id,
                'branch_id' => $jakartaBranch->id,
            ]
        );
        $cashier1->assignRole('Cashier');

        $cashier2 = User::firstOrCreate(
            ['email' => 'surabaya.cashier@freshfruit.com'],
            [
                'name' => 'Surabaya Cashier 1',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'company_id' => $company2->id,
                'branch_id' => $surabayaBranch->id,
            ]
        );
        $cashier2->assignRole('Cashier');

        $this->command->info('Admin users seeded successfully.');
        $this->command->info('-----------------------------------');
        $this->command->info('Login credentials (password: password):');
        $this->command->info('Superadmin: superadmin@juicepos.com');
        $this->command->info('Company Admin 1: admin@juicepos.com');
        $this->command->info('Company Admin 2: admin@freshfruit.com');
        $this->command->info('Branch Admin: jakarta.admin@juicepos.com');
        $this->command->info('Stock Admin: jakarta.stock@juicepos.com');
        $this->command->info('Cashier: jakarta.cashier@juicepos.com');
        $this->command->info('-----------------------------------');
    }
}
