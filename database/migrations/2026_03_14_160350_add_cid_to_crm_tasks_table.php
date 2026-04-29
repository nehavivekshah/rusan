<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_tasks', 'cid')) {
                $table->integer('cid')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('crm_tasks', 'cid')) {
                $table->dropColumn('cid');
            }
        });
    }
};
