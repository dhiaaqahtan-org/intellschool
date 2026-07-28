<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Saas\Http\Requests\StoreDemoRequest;
use Modules\Saas\Jobs\NotifyDemoRequestJob;
use Modules\Saas\Models\Landlord\DemoRequest;

class DemoRequestController extends Controller
{
    /**
     * Answers both a plain form POST and the Vue island's JSON request, so the
     * demo flow works identically with JavaScript disabled.
     */
    public function store(StoreDemoRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except('website');
        $retentionDays = max(1, min(3650, (int) config('saas.leads.retention_days', 90)));
        $hashKey = (string) config('app.key');

        $lead = DemoRequest::create([
            'name' => $data['name'],
            'school' => $data['school'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'school_size' => $data['size'] ?? null,
            'message' => $data['message'] ?? null,
            'locale' => app()->getLocale(),
            'status' => 'new',
            'consent_at' => now(),
            'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), $hashKey) : null,
            'user_agent_hash' => $request->userAgent() ? hash_hmac('sha256', $request->userAgent(), $hashKey) : null,
            'purge_after' => now()->addDays($retentionDays),
        ]);

        if (filled(config('saas.leads.notify'))) {
            try {
                NotifyDemoRequestJob::dispatch($lead->uuid)->afterCommit();
            } catch (\Throwable $dispatchError) {
                report($dispatchError);
            }
        }

        $message = __('saas::marketing.form.success');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()
            ->with('saas_demo_status', $message)
            ->withFragment('demo');
    }
}
