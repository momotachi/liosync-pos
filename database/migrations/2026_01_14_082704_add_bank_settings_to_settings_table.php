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
        // Insert bank settings
        DB::table('settings')->insert([
            [
                'key' => 'bank_name',
                'value' => 'BCA',
                'type' => 'text',
                'group' => 'payment',
                'label' => 'Bank Name',
                'description' => 'Bank name for payment transfers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_account_number',
                'value' => '1234567890',
                'type' => 'text',
                'group' => 'payment',
                'label' => 'Bank Account Number',
                'description' => 'Bank account number for payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_account_name',
                'value' => 'Cycle POS System',
                'type' => 'text',
                'group' => 'payment',
                'label' => 'Bank Account Name',
                'description' => 'Account holder name for bank transfers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['bank_name', 'bank_account_number', 'bank_account_name'])->delete();
    }
};
