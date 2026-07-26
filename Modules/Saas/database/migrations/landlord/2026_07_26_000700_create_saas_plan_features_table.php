<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Features included in each plan version (plan §8, §9.3).
 *
 * Feature codes are stable dotted identifiers: 'students.core',
 * 'finance.fees', 'hr.payroll', 'campuses.max', 'storage.gb'.
 * A limit of null means unlimited; 0 means the feature is disabled
 * for this plan version.
 */
return new class extends Migration
{
    public function getConnection(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create('saas_plan_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->index();

            // Stable feature code, e.g. 'students.core', 'campuses.max'.
            $table->string('feature_code', 80);

            $table->boolean('enabled')->default(true);

            // null = unlimited, integer = hard/soft cap.
            $table->unsignedInteger('limit_value')->nullable();

            // 'hard' = enforced at write time; 'soft' = warning only.
            $table->string('limit_type', 10)->default('hard');

            $table->timestamps();

            $table->unique(['plan_id', 'feature_code']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('saas_plan_features');
    }
};
