<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Saas\Domain\Website\ClaimGate;

class PageController extends Controller
{
    /**
     * Product facts rendered on the site come from config, never from a
     * hand-typed number in a Blade file, so every published claim has one
     * traceable source that can be re-verified after a release.
     */
    protected function facts(): array
    {
        return config('saas.facts');
    }

    public function home(): View
    {
        $claimGate = app(ClaimGate::class);

        return view('saas::marketing.home', [
            'facts' => $this->facts(),
            'modules' => $this->moduleCoverage(),
            'roles' => $this->roleCoverage(),
            'claims' => [
                'pricing' => $claimGate->pricing(),
                'mobile_ga' => $claimGate->mobileGeneralAvailability(),
                'uptime' => $claimGate->uptime(),
                'certifications' => $claimGate->certifications(),
            ],
        ]);
    }

    public function demo(): View
    {
        $sizes = collect(config('saas.leads.size_options', []))
            ->map(fn (string $value) => [
                'value' => $value,
                'label' => __("saas::marketing.form.size_options.{$value}"),
            ])
            ->all();

        return view('saas::marketing.demo', [
            'sizes' => $sizes,
        ]);
    }

    /**
     * Endpoint counts per module.
     *
     * Source: docs/instikit-modules-endpoints.md (auto-generated from the route
     * table). Regenerate and update after any release that changes routes.
     */
    protected function moduleCoverage(): array
    {
        return [
            'student' => 265, 'employee' => 200, 'academic' => 167, 'core' => 132,
            'exam' => 116, 'finance' => 115, 'transport' => 95, 'inventory' => 76,
            'reception' => 73, 'resource' => 53, 'library' => 39, 'hostel' => 30,
            'task' => 30, 'communication' => 26, 'helpdesk' => 21, 'approval' => 19,
            'asset' => 18, 'calendar' => 18, 'mess' => 18, 'contact' => 17,
            'blog' => 16, 'news' => 16, 'auth' => 16, 'activity' => 15,
            'guardian' => 13, 'form' => 12, 'recruitment' => 12, 'site' => 10,
            'post' => 10, 'gallery' => 9, 'chat' => 8, 'discipline' => 6,
            'device' => 5, 'misc' => 4,
        ];
    }

    /**
     * Permission counts per role.
     *
     * Source: docs/instikit-role-capabilities.md, generated from
     * resources/var/permission.json.
     */
    protected function roleCoverage(): array
    {
        return [
            'admin' => 644, 'principal' => 468, 'staff' => 80,
            'accountant' => 73, 'guardian' => 29, 'student' => 29,
        ];
    }
}
