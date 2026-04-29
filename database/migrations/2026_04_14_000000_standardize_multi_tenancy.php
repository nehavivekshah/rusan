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
        // Tables that need 'cid' added
        $tables = [
            'contracts',
            'opportunities',
            'interactions',
            'campaigns',
            'automations',
            'enquiries',
            'todo_lists',
            'attendances',
            'holidays',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'cid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('cid')->nullable()->index()->after('id');
                });
            }
        }

        // Standardize 'support_tickets' (rename company_id to cid or add cid)
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (Schema::hasColumn('support_tickets', 'company_id')) {
                    $table->renameColumn('company_id', 'cid');
                    // Add index if not already there (rename usually keeps index but let's be safe)
                    $table->index('cid');
                } elseif (!Schema::hasColumn('support_tickets', 'cid')) {
                    $table->unsignedBigInteger('cid')->nullable()->index()->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse support_tickets
        if (Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'cid')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->renameColumn('cid', 'company_id');
            });
        }

        $tables = [
            'contracts',
            'opportunities',
            'interactions',
            'campaigns',
            'automations',
            'enquiries',
            'todo_lists',
            'attendances',
            'holidays',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'cid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('cid');
                });
            }
        }
    }
};
