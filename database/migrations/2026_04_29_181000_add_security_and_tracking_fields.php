<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Secure Token for Proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('secure_token', 64)->nullable()->unique()->after('status');
        });

        // 2. Email Tracking for Scheduled Emails
        Schema::table('scheduled_emails', function (Blueprint $table) {
            $table->string('tracking_token', 64)->nullable()->unique()->after('id');
            $table->timestamp('opened_at')->nullable()->after('status');
            $table->timestamp('clicked_at')->nullable()->after('opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('secure_token');
        });

        Schema::table('scheduled_emails', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'opened_at', 'clicked_at']);
        });
    }
};
