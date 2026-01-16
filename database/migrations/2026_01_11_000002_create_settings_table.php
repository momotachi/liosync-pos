<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json
            $table->string('group')->default('general'); // general, pos, receipt, tax, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        // Insert default settings
        DB::table('settings')->insert([
            // General Settings
            ['key' => 'store_name', 'value' => 'JuicePOS Store', 'type' => 'text', 'group' => 'general', 'label' => 'Store Name', 'description' => 'Name of your store displayed on receipts and UI'],
            ['key' => 'store_address', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Store Address', 'description' => 'Store address for receipts'],
            ['key' => 'store_phone', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Store Phone', 'description' => 'Store phone number for receipts'],
            ['key' => 'store_email', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Store Email', 'description' => 'Store email address'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'text', 'group' => 'general', 'label' => 'Currency', 'description' => 'Default currency (USD, EUR, etc.)'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'text', 'group' => 'general', 'label' => 'Currency Symbol', 'description' => 'Currency symbol displayed'],

            // POS Settings
            ['key' => 'auto_logout_time', 'value' => '30', 'type' => 'number', 'group' => 'pos', 'label' => 'Auto Logout Time (minutes)', 'description' => 'Minutes of inactivity before auto logout'],
            ['key' => 'enable_barcode_scanner', 'value' => '1', 'type' => 'boolean', 'group' => 'pos', 'label' => 'Enable Barcode Scanner', 'description' => 'Allow barcode scanner input in POS'],
            ['key' => 'default_customer_name', 'value' => 'Walk-in Customer', 'type' => 'text', 'group' => 'pos', 'label' => 'Default Customer Name', 'description' => 'Default name for walk-in customers'],

            // Receipt Settings
            ['key' => 'receipt_header', 'value' => 'Thank you for your purchase!', 'type' => 'text', 'group' => 'receipt', 'label' => 'Receipt Header', 'description' => 'Custom header message on receipts'],
            ['key' => 'receipt_footer', 'value' => 'Please come again!', 'type' => 'text', 'group' => 'receipt', 'label' => 'Receipt Footer', 'description' => 'Custom footer message on receipts'],
            ['key' => 'receipt_width', 'value' => '80', 'type' => 'number', 'group' => 'receipt', 'label' => 'Receipt Width (mm)', 'description' => 'Thermal printer paper width (usually 58mm or 80mm)'],
            ['key' => 'show_customer_phone_on_receipt', 'value' => '1', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Show Customer Phone', 'description' => 'Display customer phone number on receipt'],
            ['key' => 'show_cashier_name_on_receipt', 'value' => '1', 'type' => 'boolean', 'group' => 'receipt', 'label' => 'Show Cashier Name', 'description' => 'Display cashier name on receipt'],

            // Tax Settings
            ['key' => 'tax_rate', 'value' => '0', 'type' => 'number', 'group' => 'tax', 'label' => 'Tax Rate (%)', 'description' => 'Default tax rate percentage (0 to disable)'],
            ['key' => 'tax_name', 'value' => 'Tax', 'type' => 'text', 'group' => 'tax', 'label' => 'Tax Name', 'description' => 'Name displayed for tax on receipts (e.g., VAT, GST, Sales Tax)'],
            ['key' => 'tax_included', 'value' => '0', 'type' => 'boolean', 'group' => 'tax', 'label' => 'Tax Included in Price', 'description' => 'Prices include tax by default'],

            // Inventory Settings
            ['key' => 'low_stock_alert', 'value' => '1', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'Enable Low Stock Alerts', 'description' => 'Show alerts for low stock items'],
            ['key' => 'auto_deduct_stock', 'value' => '1', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'Auto Deduct Stock', 'description' => 'Automatically deduct stock when orders are placed'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
