<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Jobs\SendOwnerInvitationJob;
use Modules\Saas\Mail\OwnerInvitationMail;
use Modules\Saas\Models\Landlord\Invitation;
use Modules\Saas\Models\Landlord\TenantOwner;
use Modules\Saas\Tests\TenancyTestCase;

uses(TenancyTestCase::class);

beforeEach(function () {
    config()->set('app.url', 'http://localhost');
    app('url')->forceRootUrl('http://localhost');
    config()->set('saas.hosts.marketing', 'localhost');
    config()->set('saas.signup.owner_invitations_enabled', true);
    config()->set('saas.signup.invitation_expiry_days', 3);

    $this->withServerVariables([
        'HTTP_HOST' => 'localhost',
        'SERVER_NAME' => 'localhost',
    ]);
});

function invitationFixture($test): array
{
    $makeTenant = new ReflectionMethod($test, 'makeTenant');
    $makeTenant->setAccessible(true);
    $tenant = $makeTenant->invoke($test, 'alpha', 'alpha.test');
    $userUuid = (string) Str::uuid();

    app(CurrentTenant::class)->runFor($tenant->toContext('alpha.test'), function () use ($userUuid) {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'uuid' => $userUuid,
            'name' => 'Ahmed Owner',
            'email' => 'owner@alpha.test',
            'password' => Hash::make('old-password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    $owner = TenantOwner::create([
        'tenant_uuid' => $tenant->uuid,
        'tenant_user_uuid' => $userUuid,
        'name' => 'Ahmed Owner',
        'email' => 'owner@alpha.test',
        'role' => 'owner',
        'status' => 'invited',
        'invited_at' => now(),
    ]);

    return compact('tenant', 'owner', 'userUuid');
}

it('sends a hashed expiring one-time owner invitation after provisioning', function () {
    Mail::fake();
    ['tenant' => $tenant] = invitationFixture($this);

    (new SendOwnerInvitationJob($tenant->uuid))->handle();

    $invitation = Invitation::query()->sole();

    expect($invitation->token_hash)->toHaveLength(64)
        ->and($invitation->expires_at->diffInDays(now(), absolute: true))->toBeLessThanOrEqual(3);

    Mail::assertSent(OwnerInvitationMail::class, function (OwnerInvitationMail $mail) use ($invitation) {
        $token = basename(parse_url($mail->invitationUrl, PHP_URL_PATH));

        return $mail->hasTo('owner@alpha.test')
            && hash_equals($invitation->token_hash, hash('sha256', $token));
    });
});

it('sets the password in the correct tenant and consumes the invitation once', function () {
    Mail::fake();
    ['tenant' => $tenant, 'owner' => $owner, 'userUuid' => $userUuid] = invitationFixture($this);

    (new SendOwnerInvitationJob($tenant->uuid))->handle();

    $url = null;
    Mail::assertSent(OwnerInvitationMail::class, function (OwnerInvitationMail $mail) use (&$url) {
        $url = $mail->invitationUrl;

        return true;
    });

    $path = parse_url($url, PHP_URL_PATH);
    $response = $this->post($path, [
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect('https://alpha.test/app');

    expect(Invitation::query()->sole()->accepted_at)->not->toBeNull()
        ->and($owner->fresh()->status)->toBe('active');

    app(CurrentTenant::class)->runFor($tenant->toContext('alpha.test'), function () use ($userUuid) {
        $user = DB::table('users')->where('uuid', $userUuid)->first();

        expect(Hash::check('new-secure-password', $user->password))->toBeTrue()
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->status)->toBe('activated');
    });

    $this->post($path, [
        'password' => 'another-password',
        'password_confirmation' => 'another-password',
    ])->assertGone();
});

it('rejects an expired invitation without touching the tenant user', function () {
    ['tenant' => $tenant] = invitationFixture($this);

    ['token' => $token] = Invitation::createWithToken([
        'tenant_uuid' => $tenant->uuid,
        'email' => 'owner@alpha.test',
        'role' => 'owner',
        'expires_at' => now()->subMinute(),
    ]);

    $this->get('/en/invitation/'.$token)->assertGone();
});
