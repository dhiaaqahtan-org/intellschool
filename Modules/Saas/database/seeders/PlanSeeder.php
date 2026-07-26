<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Saas\Models\Landlord\Plan;
use Modules\Saas\Models\Landlord\PlanFeature;

/**
 * Seeds the default SaaS plans and their features (plan §9.3).
 *
 * Plans are based on real customer segments:
 *  - Starter: one campus, bounded students/staff/storage.
 *  - Growth: several campuses, advanced finance/HR/reporting, API/mobile.
 *  - Enterprise: negotiated limits, SSO, dedicated infrastructure.
 *
 * Prices are PLACEHOLDERS. Do not publish until commercial pricing is approved.
 * A price or feature change creates a NEW plan version, never a silent rewrite.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->createStarterPlan();
        $this->createGrowthPlan();
        $this->createEnterprisePlan();
    }

    private function createStarterPlan(): void
    {
        $plan = Plan::firstOrCreate(
            ['plan_code' => 'starter', 'version' => 1],
            [
                'display_name' => 'Starter School',
                'description' => 'For a single-campus school getting started with digital management.',
                'billing_interval' => 'monthly',
                'currency' => 'USD',
                'price_cents' => 4900, // $49.00 — PLACEHOLDER
                'trial_days' => 14,
                'active_from' => now(),
                'active_until' => null,
                'is_public' => true,
            ]
        );

        $features = [
            // Core student management
            ['feature_code' => 'students.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.admissions', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.attendance', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.promotion', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Academics
            ['feature_code' => 'academics.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.exams', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.timetable', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Finance — basic fees only
            ['feature_code' => 'finance.fees', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.payroll', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.accounting', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // HR — basic only
            ['feature_code' => 'hr.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'hr.leave', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // Operations
            ['feature_code' => 'transport.routes', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'library.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'inventory.core', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // Communication — email only
            ['feature_code' => 'communication.sms', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.email', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.push', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // Website
            ['feature_code' => 'website.cms', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Mobile/API — not included
            ['feature_code' => 'mobile.offline', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'api.access', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // Reports — basic only
            ['feature_code' => 'reports.advanced', 'enabled' => false, 'limit_value' => null, 'limit_type' => null],

            // Limits
            ['feature_code' => 'storage.gb', 'enabled' => true, 'limit_value' => 5, 'limit_type' => 'hard'],
            ['feature_code' => 'campuses.max', 'enabled' => true, 'limit_value' => 1, 'limit_type' => 'hard'],
            ['feature_code' => 'users.max', 'enabled' => true, 'limit_value' => 500, 'limit_type' => 'soft'],
        ];

        $this->syncFeatures($plan, $features);
    }

    private function createGrowthPlan(): void
    {
        $plan = Plan::firstOrCreate(
            ['plan_code' => 'growth', 'version' => 1],
            [
                'display_name' => 'Growth School',
                'description' => 'For multi-campus schools needing advanced finance, HR, reporting, and mobile access.',
                'billing_interval' => 'monthly',
                'currency' => 'USD',
                'price_cents' => 14900, // $149.00 — PLACEHOLDER
                'trial_days' => 14,
                'active_from' => now(),
                'active_until' => null,
                'is_public' => true,
            ]
        );

        $features = [
            // Core student management — unlimited
            ['feature_code' => 'students.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.admissions', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.attendance', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.promotion', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Academics — full
            ['feature_code' => 'academics.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.exams', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.timetable', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Finance — full
            ['feature_code' => 'finance.fees', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.payroll', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.accounting', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // HR — full
            ['feature_code' => 'hr.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'hr.leave', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Operations — full
            ['feature_code' => 'transport.routes', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'library.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'inventory.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Communication — full
            ['feature_code' => 'communication.sms', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.email', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.push', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Website
            ['feature_code' => 'website.cms', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Mobile/API — included
            ['feature_code' => 'mobile.offline', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'api.access', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Reports — advanced
            ['feature_code' => 'reports.advanced', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Limits — higher
            ['feature_code' => 'storage.gb', 'enabled' => true, 'limit_value' => 50, 'limit_type' => 'hard'],
            ['feature_code' => 'campuses.max', 'enabled' => true, 'limit_value' => 5, 'limit_type' => 'hard'],
            ['feature_code' => 'users.max', 'enabled' => true, 'limit_value' => 5000, 'limit_type' => 'soft'],
        ];

        $this->syncFeatures($plan, $features);
    }

    private function createEnterprisePlan(): void
    {
        $plan = Plan::firstOrCreate(
            ['plan_code' => 'enterprise', 'version' => 1],
            [
                'display_name' => 'Enterprise',
                'description' => 'For large institutions and groups requiring negotiated limits, SSO, dedicated infrastructure, and premium support.',
                'billing_interval' => 'annual',
                'currency' => 'USD',
                'price_cents' => 0, // Custom pricing — contact sales
                'trial_days' => 0,
                'active_from' => now(),
                'active_until' => null,
                'is_public' => true,
            ]
        );

        $features = [
            // Everything enabled, unlimited
            ['feature_code' => 'students.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.admissions', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.attendance', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'students.promotion', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.exams', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'academics.timetable', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.fees', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.payroll', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'finance.accounting', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'hr.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'hr.leave', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'transport.routes', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'library.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'inventory.core', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.sms', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.email', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'communication.push', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'website.cms', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'mobile.offline', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'api.access', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'reports.advanced', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],

            // Enterprise limits — effectively unlimited
            ['feature_code' => 'storage.gb', 'enabled' => true, 'limit_value' => 500, 'limit_type' => 'soft'],
            ['feature_code' => 'campuses.max', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
            ['feature_code' => 'users.max', 'enabled' => true, 'limit_value' => null, 'limit_type' => null],
        ];

        $this->syncFeatures($plan, $features);
    }

    /**
     * Idempotently sync features for a plan version.
     */
    private function syncFeatures(Plan $plan, array $features): void
    {
        foreach ($features as $feature) {
            PlanFeature::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'feature_code' => $feature['feature_code'],
                ],
                [
                    'enabled' => $feature['enabled'],
                    'limit_value' => $feature['limit_value'],
                    'limit_type' => $feature['limit_type'],
                ]
            );
        }
    }
}
