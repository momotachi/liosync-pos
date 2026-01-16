<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();

        // Define all settings (without currency)
        $allSettings = [
            // General Settings
            [
                'key' => 'store_name',
                'value' => 'JuicePOS Store',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Store Name',
                'description' => 'Name of your store displayed on receipts',
            ],
            [
                'key' => 'store_address',
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Store Address',
                'description' => 'Store address for receipts',
            ],
            [
                'key' => 'store_phone',
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Store Phone',
                'description' => 'Contact phone number',
            ],
            [
                'key' => 'store_email',
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Store Email',
                'description' => 'Contact email address',
            ],

            // POS Settings
            [
                'key' => 'auto_logout_time',
                'value' => '30',
                'type' => 'number',
                'group' => 'pos',
                'label' => 'Auto Logout Time (minutes)',
                'description' => 'Automatically logout cashier after inactivity',
            ],
            [
                'key' => 'enable_barcode_scanner',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'pos',
                'label' => 'Enable Barcode Scanner',
                'description' => 'Allow barcode scanning in POS',
            ],
            [
                'key' => 'default_customer_name',
                'value' => 'Walk-in Customer',
                'type' => 'text',
                'group' => 'pos',
                'label' => 'Default Customer Name',
                'description' => 'Default name for walk-in customers',
            ],

            // Receipt Settings
            [
                'key' => 'receipt_header',
                'value' => 'Thank you for your purchase!',
                'type' => 'text',
                'group' => 'receipt',
                'label' => 'Receipt Header',
                'description' => 'Text displayed at the top of receipts',
            ],
            [
                'key' => 'receipt_footer',
                'value' => 'Please come again!',
                'type' => 'text',
                'group' => 'receipt',
                'label' => 'Receipt Footer',
                'description' => 'Text displayed at the bottom of receipts',
            ],
            [
                'key' => 'receipt_width',
                'value' => '80',
                'type' => 'number',
                'group' => 'receipt',
                'label' => 'Receipt Width (mm)',
                'description' => 'Width of thermal printer paper',
            ],
            [
                'key' => 'show_customer_phone_on_receipt',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'receipt',
                'label' => 'Show Customer Phone',
                'description' => 'Display customer phone number on receipt',
            ],
            [
                'key' => 'show_cashier_name_on_receipt',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'receipt',
                'label' => 'Show Cashier Name',
                'description' => 'Display cashier name on receipt',
            ],

            // Tax Settings
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'number',
                'group' => 'tax',
                'label' => 'Tax Rate (%)',
                'description' => 'Default tax rate percentage',
            ],
            [
                'key' => 'tax_name',
                'value' => 'Tax',
                'type' => 'text',
                'group' => 'tax',
                'label' => 'Tax Name',
                'description' => 'Name displayed for tax on receipts',
            ],
            [
                'key' => 'tax_included',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'tax',
                'label' => 'Tax Included in Price',
                'description' => 'Prices include tax by default',
            ],

            // Inventory Settings
            [
                'key' => 'low_stock_alert',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Low Stock Alert',
                'description' => 'Show alerts when items are low in stock',
            ],
            [
                'key' => 'auto_deduct_stock',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Auto Deduct Stock',
                'description' => 'Automatically deduct stock when items are sold',
            ],
        ];

        // Create settings for each branch
        foreach ($branches as $branch) {
            foreach ($allSettings as $setting) {
                Setting::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'key' => $setting['key'],
                    ],
                    [
                        'value' => $setting['value'],
                        'type' => $setting['type'],
                        'group' => $setting['group'],
                        'label' => $setting['label'],
                        'description' => $setting['description'],
                    ]
                );
            }
        }

        $this->command->info('Settings seeded for ' . $branches->count() . ' branches.');
    }
}
