<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // 2. Seed companies and branches
        $this->call(CompanySeeder::class);

        // 3. Seed admin users (including company admins)
        $this->call(AdminUserSeeder::class);

        // 4. Seed subscription plans
        $this->call(SubscriptionPlanSeeder::class);

        // Admin User (legacy)
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        // Set company type to resto for testing POS flow
        $this->call(SetRestoCompanyTypeSeeder::class);

        // Categories
        $juice = \App\Models\Category::create(['name' => 'Fresh Juices', 'type' => 'product']);
        $smoothies = \App\Models\Category::create(['name' => 'Smoothies', 'type' => 'product']);
        $snacks = \App\Models\Category::create(['name' => 'Snacks', 'type' => 'product']);

        // Products
        \App\Models\Product::create(['category_id' => $juice->id, 'name' => 'Mango Magic', 'price' => 5.50, 'is_active' => true, 'image' => null]);
        \App\Models\Product::create(['category_id' => $juice->id, 'name' => 'Avocado Dream', 'price' => 6.50, 'is_active' => true, 'image' => null]);
        \App\Models\Product::create(['category_id' => $smoothies->id, 'name' => 'Berry Blast', 'price' => 5.00, 'is_active' => true, 'image' => null]);
        \App\Models\Product::create(['category_id' => $snacks->id, 'name' => 'Banana', 'price' => 1.50, 'is_active' => true, 'image' => null]);
    }
}
