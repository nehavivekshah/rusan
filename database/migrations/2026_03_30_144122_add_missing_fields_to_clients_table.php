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
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'industry')) {
                $table->string('industry')->nullable();
            }
            if (!Schema::hasColumn('clients', 'position')) {
                $table->string('position')->nullable();
            }
            if (!Schema::hasColumn('clients', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('clients', 'values')) {
                $table->text('values')->nullable();
            }
            if (!Schema::hasColumn('clients', 'language')) {
                $table->string('language')->nullable();
            }
            if (!Schema::hasColumn('clients', 'tags')) {
                $table->string('tags')->nullable();
            }
            if (!Schema::hasColumn('clients', 'lifecycle_stage')) {
                $table->string('lifecycle_stage')->nullable();
            }
            if (!Schema::hasColumn('clients', 'source')) {
                $table->string('source')->nullable();
            }
            if (!Schema::hasColumn('clients', 'purpose')) {
                $table->string('purpose')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'industry', 'position', 'website', 'values', 'language', 'tags', 'lifecycle_stage', 'source', 'purpose'
            ]);
        });
    }
};
