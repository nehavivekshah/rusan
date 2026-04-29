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
        Schema::table('proposals', function (Blueprint $table) {
            $table->text('tags')->nullable()->after('status');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->string('gst_no')->nullable()->after('industry');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pdf_logo')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
             $table->dropColumn('tags');
        });
        Schema::table('leads', function (Blueprint $table) {
             $table->dropColumn('gst_no');
        });
        Schema::table('companies', function (Blueprint $table) {
             $table->dropColumn('pdf_logo');
        });
    }
};
