<?php

use App\Models\Chat\Chat;
use App\Support\SetConfig;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Runs at BroadcastServiceProvider boot — before any middleware, so before
// ResolveTenant has decided which school this request belongs to.
//
// Under multi-tenancy that is the wrong place to read school config from. The
// default connection at boot is the control plane, so this would query the
// landlord database and push ITS values — app name, asset URL, auth settings,
// SMTP credentials, Pusher keys, social login secrets — into runtime config for
// whichever school is about to be served. On a control plane that holds no ERP
// schema at all it is worse than wrong: `configs` does not exist there, and
// every request throws before it reaches a route.
//
// App\Http\Middleware\Init already does exactly this work per request, after the
// tenant connection is live and skipping control-plane hosts, so there is
// nothing to replace it with — the boot-time call is a single-tenant shortcut.
if (! app()->environment('testing') && ! config('saas.tenancy.enabled', false)) {
    (new SetConfig)->set();
}

Broadcast::channel('chats.{chatUuid}', function ($user, $chatUuid) {
    return Chat::query()
        ->where('uuid', $chatUuid)
        ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
        ->exists();
});

Broadcast::channel('users.{uuid}', function ($user, $userUuid) {
    return $user->uuid == $userUuid;
});
