<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add company_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');

            $table->index('company_id');
            $table->index('branch_id');
        });

        // Add company_id to purchases table
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');

            $table->index('company_id');
            $table->index('branch_id');
        });

        // Add company_id to stock_transactions table
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');

            $table->index('company_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};
