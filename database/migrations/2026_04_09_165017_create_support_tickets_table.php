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
        Schema::create('support_tickets', function (Blueprint $col) {
            $col->id();
            $col->string('ticket_no')->unique();
            $col->unsignedBigInteger('company_id');
            $col->string('subject');
            $col->text('description')->nullable();
            $col->string('priority')->default('Medium'); // Low, Medium, High
            $col->integer('status')->default(0); // 0: Open, 1: Processed, 2: Closed
            $col->timestamps();

            // Foreign key (optional, depends on project standard)
            // $col->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
