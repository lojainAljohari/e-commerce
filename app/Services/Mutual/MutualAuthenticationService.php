<?php

namespace App\Services\Mutual;

use App\DTOs\V1\RefreshDTO;
use App\Models\Device;

class MutualAuthenticationService
{
    public function __construct(protected TokenDeviceManagementService $token_service) {}

    public function refresh(RefreshDTO $refreshDTO): array
    {
        $tokens = $this->token_service->refreshAccessToken($refreshDTO);

        if (blank($tokens)) {
            return [
                'status' => false,
                'data' => __('Invalid or expired refresh token'),
            ];
        }

        return [
            'status' => true,
            'data' => $tokens,
        ];
    }

    public function logout(): void
    {
        $device = Device::where('identifier', request()->header('device'))
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $device->delete();
    }

    public function logoutOtherDevices(): void
    {
        $user = auth()->user();

        $user->devices()->where('identifier', '!=', request()->header('device'))->delete();
    }

    public function logoutAllDevices(): void
    {
        $user = auth()->user();

        $user->devices()->delete();
    }
}
