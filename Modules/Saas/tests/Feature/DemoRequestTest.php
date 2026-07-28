<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Modules\Saas\Jobs\NotifyDemoRequestJob;
use Modules\Saas\Mail\DemoRequestReceived;
use Modules\Saas\Models\Landlord\DemoRequest;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

beforeEach(function () {
    config()->set('app.url', 'http://localhost');
    app('url')->forceRootUrl('http://localhost');
    config()->set('saas.hosts.marketing', 'localhost');
    config()->set('saas.leads.retention_days', 30);
    config()->set('saas.leads.notify', 'sales@example.com');
    $this->withServerVariables(['HTTP_HOST' => 'localhost', 'SERVER_NAME' => 'localhost']);
});

function demoPayload(): array
{
    return [
        'name' => 'Aisha Saleh',
        'school' => 'Al Noor School',
        'email' => 'aisha@gmail.com',
        'size' => '300_to_800',
        'message' => 'We need multi-campus reporting.',
        'consent' => true,
    ];
}

it('persists consented demo requests with bounded retention and queues notification', function () {
    Bus::fake();

    $this->withHeader('User-Agent', 'Demo Browser')
        ->postJson('/en/demo', demoPayload())
        ->assertCreated()
        ->assertJson(['message' => __('saas::marketing.form.success', locale: 'en')]);

    $lead = DemoRequest::query()->sole();

    expect($lead->name)->toBe('Aisha Saleh')
        ->and($lead->school_size)->toBe('300_to_800')
        ->and($lead->consent_at)->not->toBeNull()
        ->and($lead->ip_hash)->toHaveLength(64)
        ->and($lead->user_agent_hash)->toHaveLength(64)
        ->and($lead->purge_after->isBetween(now()->addDays(29), now()->addDays(31)))->toBeTrue();

    Bus::assertDispatched(
        NotifyDemoRequestJob::class,
        fn (NotifyDemoRequestJob $job) => $job->demoRequestUuid === $lead->uuid
    );
});

it('sends the queued notification once and records delivery', function () {
    Mail::fake();
    $lead = DemoRequest::create([
        ...demoPayload(),
        'school_size' => '300_to_800',
        'locale' => 'en',
        'status' => 'new',
        'consent_at' => now(),
        'purge_after' => now()->addDays(30),
    ]);

    (new NotifyDemoRequestJob($lead->uuid))->handle();
    (new NotifyDemoRequestJob($lead->uuid))->handle();

    Mail::assertSent(DemoRequestReceived::class, 1);
    expect($lead->fresh()->status)->toBe('notified')
        ->and($lead->fresh()->notified_at)->not->toBeNull();
});

it('prunes only requests whose retention deadline has passed', function () {
    foreach ([now()->subMinute(), now()->addDay()] as $purgeAfter) {
        DemoRequest::create([
            ...demoPayload(),
            'locale' => 'en',
            'status' => 'new',
            'consent_at' => now(),
            'purge_after' => $purgeAfter,
        ]);
    }

    $this->artisan('saas:prune-demo-requests')->assertSuccessful();

    expect(DemoRequest::count())->toBe(1)
        ->and(DemoRequest::first()->purge_after->isFuture())->toBeTrue();
});

it('rejects unrecognized school size codes', function () {
    $payload = demoPayload();
    $payload['size'] = 'unbounded';

    $this->postJson('/en/demo', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('size');
});
