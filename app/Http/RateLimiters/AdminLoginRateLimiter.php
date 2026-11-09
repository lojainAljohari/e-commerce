<?php

namespace App\Http\RateLimiters;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AdminLoginRateLimiter
{
    public function register(): void
    {
        RateLimiter::for('admin_login', function (Request $request) {
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
                Limit::perMinutes(5, 8)->by("admin_login:ip_email:{$ip}|{$emailKey}"),
                Limit::perMinutes(1440, 20)->by("admin_login:email:{$emailKey}"),
                Limit::perMinutes(1440, 60)->by("admin_login:ip:{$ip}"),
            ];
        });
    }
}
