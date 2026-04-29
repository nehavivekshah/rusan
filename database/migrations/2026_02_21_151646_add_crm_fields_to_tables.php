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
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('score')->nullable()->after('status');
            $table->boolean('is_duplicate')->default(false)->after('status');
            $table->string('source')->nullable()->after('status');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('lifecycle_stage')->nullable()->after('name');
            $table->string('industry')->nullable()->after('name');
            $table->string('website')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['score', 'is_duplicate', 'source']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['lifecycle_stage', 'industry', 'website']);
        });
    }
};
