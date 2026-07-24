<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginAsSupportController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        if (auth()->check()) {
            abort(404);
        }

        if (! config('config.system.enable_author_support')) {
            abort(404);
        }

        $storedToken = Cache::get('author_support_login_token');

        if (! $storedToken || ! Hash::check($token, $storedToken)) {
            abort(404);
        }

        Cache::forget('author_support_login_token');

        $user = User::query()
            ->where('meta->is_default', true)
            ->firstOrFail();

        \Auth::login($user);

        return redirect('/app');
    }
}
