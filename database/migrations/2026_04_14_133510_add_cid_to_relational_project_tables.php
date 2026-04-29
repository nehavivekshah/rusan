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
        $tables = [
            'eselicenses', 
            'customer_departments', 
            'invoice_items', 
            'proposal_items', 
            'proposal_signatures'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'cid')) {
                        $table->unsignedBigInteger('cid')->nullable()->index();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'eselicenses', 
            'customer_departments', 
            'invoice_items', 
            'proposal_items', 
            'proposal_signatures'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'cid')) {
                        $table->dropColumn('cid');
                    }
                });
            }
        }
    }
};
