<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessTokenDeviceMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $access_token = $request->user()->currentAccessToken();

        if (! $access_token || ! isset($access_token->device_id)) {
            return $this->failed(__('For Security Reasons, We Want You To Login Again Please'));
        }

        $device = Device::find($access_token->device_id);

        if (! $device || $request->header('device') != $device->identifier) {
            if ($device) {
                $device->delete();
            }

            return $this->failed(__('For Security Reasons, We Want You To Login Again Please'));
        }

        return $next($request);
    }
}
