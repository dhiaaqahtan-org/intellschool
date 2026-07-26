<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Saas\Http\Requests\StoreDemoRequest;

class DemoRequestController extends Controller
{
    /**
     * Answers both a plain form POST and the Vue island's JSON request, so the
     * demo flow works identically with JavaScript disabled.
     */
    public function store(StoreDemoRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except('website');

        // TODO(Phase 7): persist to the landlord database and notify sales.
        // Deliberately not wired to a mailer or CRM yet — an unreviewed lead
        // pipeline would be storing personal data without a retention policy.
        // Until then, record only that a request arrived, never its contents.
        Log::channel(config('logging.default'))->info('saas.demo_request.received', [
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'locale'  => app()->getLocale(),
        ]);

        $message = __('saas::marketing.form.success');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()
            ->with('saas_demo_status', $message)
            ->withFragment('demo');
    }
}
