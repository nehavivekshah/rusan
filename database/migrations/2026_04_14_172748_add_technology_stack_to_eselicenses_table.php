<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to eselicenses table.
     * The original table was created outside Laravel migrations,
     * so we add the columns that the manageLicensePost controller needs.
     */
    public function up(): void
    {
        Schema::table('eselicenses', function (Blueprint $table) {
            if (!Schema::hasColumn('eselicenses', 'technology_stack')) {
                $table->string('technology_stack')->nullable()->after('expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eselicenses', function (Blueprint $table) {
            if (Schema::hasColumn('eselicenses', 'technology_stack')) {
                $table->dropColumn('technology_stack');
            }
        });
    }
};
