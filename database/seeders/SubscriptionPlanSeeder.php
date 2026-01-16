<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses with single branch.',
                'price' => 500000,
                'max_branches' => 1,
                'max_users' => 5,
                'features' => [
                    'pos' => true,
                    'basic_reports' => true,
                    'inventory_management' => true,
                    'multi_branch' => false,
                    'advanced_reports' => false,
                    'api_access' => false,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses with multiple locations.',
                'price' => 1500000,
                'max_branches' => 5,
                'max_users' => 20,
                'features' => [
                    'pos' => true,
                    'basic_reports' => true,
                    'inventory_management' => true,
                    'multi_branch' => true,
                    'advanced_reports' => true,
                    'api_access' => false,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Full-featured solution for large organizations.',
                'price' => 5000000,
                'max_branches' => null, // unlimited
                'max_users' => null, // unlimited
                'features' => [
                    'pos' => true,
                    'basic_reports' => true,
                    'inventory_management' => true,
                    'multi_branch' => true,
                    'advanced_reports' => true,
                    'api_access' => true,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('Subscription plans seeded successfully.');
    }
}
