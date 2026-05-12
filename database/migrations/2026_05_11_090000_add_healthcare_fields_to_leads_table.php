<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds healthcare/tobacco-cessation and extended lead fields
     * to match the complete lead form specification.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // ── Lead Information: Name split ──
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');

            // ── Lead Information: Demographics ──
            $table->string('gender')->nullable()->after('last_name');
            $table->date('dob')->nullable()->after('gender');
            $table->string('progress')->nullable()->after('dob');

            // ── Lead Information: Product & Communication ──
            $table->string('interested_product')->nullable()->after('industry');
            $table->boolean('first_call')->default(false)->after('interested_product');
            $table->boolean('sms_opt')->default(false)->after('first_call');

            // ── Additional Information: Call Tracking ──
            $table->string('lead_state')->nullable()->after('status');
            $table->string('last_call_feedback')->nullable()->after('lead_state');
            $table->text('last_call_comment')->nullable()->after('last_call_feedback');
            $table->datetime('next_call_date')->nullable()->after('last_call_comment');
            $table->string('marketing_source')->nullable()->after('next_call_date');

            // ── Additional Information: Healthcare / Tobacco ──
            $table->integer('age')->nullable()->after('marketing_source');
            $table->integer('consumption_years')->nullable()->after('age');
            $table->integer('tobacco_frequency')->nullable()->after('consumption_years');
            $table->string('craving_for_smoking')->nullable()->after('tobacco_frequency');
            $table->string('problem_smoking')->nullable()->after('craving_for_smoking');
            $table->string('experience_intense_craving')->nullable()->after('problem_smoking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'middle_name', 'last_name',
                'gender', 'dob', 'progress',
                'interested_product', 'first_call', 'sms_opt',
                'lead_state', 'last_call_feedback', 'last_call_comment',
                'next_call_date', 'marketing_source',
                'age', 'consumption_years', 'tobacco_frequency',
                'craving_for_smoking', 'problem_smoking', 'experience_intense_craving',
            ]);
        });
    }
};
