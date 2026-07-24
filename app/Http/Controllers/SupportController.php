<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! auth()->user()->is_default) {
            throw ValidationException::withMessages([
                'message' => trans('user.errors.permission_denied'),
            ]);
        }

        $supportToken = Str::random(32);

        Cache::put(
            'author_support_login_token',
            Hash::make($supportToken),
            now()->addMinutes(10)
        );

        return response()->json([
            'message' => 'Support token generated successfully. You can share it with support team.',
            'token' => $supportToken,
        ]);
    }
}
