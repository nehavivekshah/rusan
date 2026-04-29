<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('third_party_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->index();
            $table->string('provider'); // exotel, whatsapp, etc.
            $table->text('api_key')->nullable();
            $table->text('api_token')->nullable();
            $table->string('account_sid')->nullable();
            $table->string('from_number')->nullable();
            $table->text('additional_config')->nullable(); // JSON for miscellaneous settings
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('third_party_settings');
    }
};
