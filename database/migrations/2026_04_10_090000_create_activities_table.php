<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('cid')->nullable()->index();          // company id — for fast scoping
            $table->string('type', 80);                          // e.g. Lead Created, Invoice Paid
            $table->string('module', 50)->nullable();            // leads | clients | invoices | tasks | proposals …
            $table->unsignedBigInteger('subject_id')->nullable();// FK to the related record
            $table->string('subject_label')->nullable();         // human-readable name of the record
            $table->text('description')->nullable();             // full sentence for the feed
            $table->string('value')->nullable();                 // optional monetary / numeric value
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
