<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('batchNo')->nullable()->after('name');
        });

        // Migrate existing batchNo from clients to their projects
        DB::statement('
            UPDATE projects
            INNER JOIN clients ON projects.client_id = clients.id
            SET projects.batchNo = clients.batchNo
            WHERE clients.batchNo IS NOT NULL AND clients.batchNo != ""
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('batchNo');
        });
    }
};
