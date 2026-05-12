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
        Schema::create('email_inboxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email');
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl');
            $table->string('username');
            $table->string('password');
            $table->string('status')->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('received_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->index();
            $table->unsignedBigInteger('inbox_id')->index();
            $table->string('message_id')->unique();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject')->nullable();
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->timestamp('received_at');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_emails');
        Schema::dropIfExists('email_inboxes');
    }
};
