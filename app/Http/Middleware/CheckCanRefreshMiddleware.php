<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCanRefreshMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->check()) {
            return $this->failed(__('You are already authenticated'));
        }

        $device = Device::where('identifier', $request->header('device'))->first();

        if (! isset($device)) {
            return $this->failed(__('Un valid device'));
        }

        if (now()->gt($device->token_expires_at)) {
            return $this->failed(__('Un valid or expired refresh token'));
        }

        return $next($request);
    }
}
