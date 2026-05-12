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
        Schema::table('automations', function (Blueprint $table) {
            $table->text('conditions')->nullable()->after('trigger_event'); // JSON or logic
            $table->text('action_payload')->nullable()->after('action'); // JSON parameters for action
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->dropColumn(['conditions', 'action_payload']);
        });
    }
};
