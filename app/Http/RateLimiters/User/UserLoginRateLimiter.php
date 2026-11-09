<?php

namespace App\Http\RateLimiters\User;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class UserLoginRateLimiter
{
    public function register(): void
    {
        RateLimiter::for('user_login', function (Request $request) {
            $rawEmail = $request->input('email');

            $isString = is_string($rawEmail);
            $isValidLength = $isString && strlen($rawEmail) <= 255;

            if ($isValidLength && trim($rawEmail) !== '') {
                $emailKey = hash('sha256', strtolower(trim($rawEmail)));
            } else {
                $emailKey = 'invalid_'.$request->ip();
            }

            $ip = $request->ip();

            return [
                Limit::perMinutes(5, 8)->by("user_login:ip_email:{$ip}|{$emailKey}"),
                Limit::perMinutes(1440, 20)->by("user_login:email:{$emailKey}"),
                Limit::perMinutes(1440, 60)->by("user_login:ip:{$ip}"),
            ];
        });
    }
}
