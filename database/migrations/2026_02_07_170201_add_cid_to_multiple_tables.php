<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add cid column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('cid')->nullable()->after('id');
        });

        // Add cid column to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('cid')->nullable()->after('id');
        });

        // Check if other tables need cid column
        if (!Schema::hasColumn('clients', 'cid')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->unsignedBigInteger('cid')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('proposals', 'cid')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->unsignedBigInteger('cid')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('invoices', 'cid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('cid')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('projects', 'cid')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->unsignedBigInteger('cid')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('recoveries', 'cid')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->unsignedBigInteger('cid')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cid');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('cid');
        });

        if (Schema::hasColumn('clients', 'cid')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('cid');
            });
        }

        if (Schema::hasColumn('proposals', 'cid')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('cid');
            });
        }

        if (Schema::hasColumn('invoices', 'cid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('cid');
            });
        }

        if (Schema::hasColumn('projects', 'cid')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('cid');
            });
        }

        if (Schema::hasColumn('recoveries', 'cid')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->dropColumn('cid');
            });
        }
    }
};
