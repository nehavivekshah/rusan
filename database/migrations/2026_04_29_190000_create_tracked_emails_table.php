<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracked_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->index();
            $table->string('recipient');
            $table->string('subject');
            $table->string('tracking_token')->unique();
            $table->integer('opens')->default(0);
            $table->integer('clicks')->default(0);
            $table->timestamp('last_open')->nullable();
            $table->timestamp('last_click')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracked_emails');
    }
};
