<?php

namespace Modules\Saas\Http\Controllers\Marketing;

use App\Enums\UserStatus;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Domain\Identity\InvitationToken;
use Modules\Saas\Models\Landlord\Invitation;
use Modules\Saas\Models\Landlord\TenantOwner;

class OwnerInvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = Invitation::findByToken($token);

        abort_if($invitation === null || ! $invitation->isValid(), 410);

        $tenant = $invitation->tenant;
        abort_if($tenant === null || ! $tenant->isServable(), 409);

        return view('saas::marketing.owner-invitation', compact('invitation', 'tenant', 'token'));
    }

    public function accept(Request $request, string $token, CurrentTenant $currentTenant): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $landlord = DB::connection(config('saas.database.landlord_connection', 'landlord'));

        $hostname = $landlord->transaction(function () use ($token, $validated, $currentTenant) {
            $invitation = Invitation::query()
                ->with(['tenant.database', 'tenant.domains'])
                ->where('token_hash', InvitationToken::fromPlainText($token)->digest())
                ->lockForUpdate()
                ->first();

            abort_if($invitation === null || ! $invitation->isValid(), 410);

            $tenant = $invitation->tenant;
            abort_if($tenant === null || ! $tenant->isServable(), 409);

            $owner = TenantOwner::query()
                ->where('tenant_uuid', $tenant->uuid)
                ->where('email', $invitation->email)
                ->where('role', 'owner')
                ->firstOrFail();

            $domain = $tenant->primaryDomain();
            abort_if($domain === null, 409);

            $userUuid = $currentTenant->runFor($tenant->toContext($domain->hostname), function () use ($owner, $invitation, $validated) {
                $query = DB::table('users');

                if ($owner->tenant_user_uuid !== null) {
                    $query->where('uuid', $owner->tenant_user_uuid);
                } else {
                    $query->where('email', $invitation->email);
                }

                $user = $query->first();
                abort_if($user === null, 409);

                DB::table('users')->where('id', $user->id)->update([
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                    'status' => UserStatus::ACTIVATED->value,
                    'updated_at' => now(),
                ]);

                return (string) $user->uuid;
            });

            $invitation->accept();
            $owner->forceFill(['tenant_user_uuid' => $userUuid])->save();
            $owner->accept();

            return $domain->hostname;
        });

        return redirect()->away('https://'.$hostname.'/app');
    }
}
