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
        Schema::table('leads', function (Blueprint $table) {
            // Add missing fields that are NOT in the table yet
            if (!Schema::hasColumn('leads', 'email_opt_out')) {
                $table->boolean('email_opt_out')->default(0)->after('website');
            }
            if (!Schema::hasColumn('leads', 'sms_opt_out')) {
                $table->boolean('sms_opt_out')->default(0)->after('email_opt_out');
            }
            if (!Schema::hasColumn('leads', 'city')) {
                $table->string('city')->nullable()->after('sms_opt_out');
            }
            if (!Schema::hasColumn('leads', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('leads', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            if (!Schema::hasColumn('leads', 'pin_code')) {
                $table->string('pin_code')->nullable()->after('country');
            }
            if (!Schema::hasColumn('leads', 'address')) {
                $table->string('address')->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'email_opt_out', 'sms_opt_out', 'city', 'state', 'country', 'pin_code', 'address'
            ]);
        });
    }
};
