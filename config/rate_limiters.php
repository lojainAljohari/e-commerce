<?php

use App\Http\RateLimiters\AdminLoginRateLimiter;
use App\Http\RateLimiters\EmailVerificationRateLimiter;
use App\Http\RateLimiters\User\UserLoginRateLimiter;

return [
    UserLoginRateLimiter::class,
    AdminLoginRateLimiter::class,
    EmailVerificationRateLimiter::class,
];
