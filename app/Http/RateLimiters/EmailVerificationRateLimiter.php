<?php

namespace App\Http\RateLimiters;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationRateLimiter
{
    public function register(): void
    {
        RateLimiter::for('email_verification', function (Request $request) {
            $user = $request->user();
            $key = $user ? 'user:'.$user->id : 'ip:'.$request->ip();

            return [
                Limit::perMinutes(1, 6)->by($key),
                Limit::perMinutes(60, 30)->by($key.':hour'),
            ];
        });

        RateLimiter::for('resend_verification', function (Request $request) {
            $user = $request->user();
            $key = $user ? 'user:'.$user->id : 'ip:'.$request->ip();

            return [
                Limit::perMinutes(1, 3)->by($key),
                Limit::perMinutes(60, 10)->by($key.':hour'),
            ];
        });
    }
}
