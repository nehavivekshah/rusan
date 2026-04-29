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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('category', 255)->nullable()->after('name');
            $table->date('start_date')->nullable()->after('category');
            $table->date('deadline')->nullable()->after('start_date');
            $table->text('tags')->nullable()->after('note');
            
            // If status is missing, add it (using integer as per existing filter logic)
            if (!Schema::hasColumn('projects', 'status')) {
                $table->integer('status')->default(1)->after('deadline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['category', 'start_date', 'deadline', 'tags']);
        });
    }
};
