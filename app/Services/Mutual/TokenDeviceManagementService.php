<?php

namespace App\Services\Mutual;

use App\DTOs\V1\AuthInfoDTO;
use App\DTOs\V1\NewRefreshDTO;
use App\DTOs\V1\RefreshDTO;
use App\Enums\TokenNameEnum;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TokenDeviceManagementService
{
    public function issueTokenPair(User $user, TokenNameEnum $tokenName, ?string $fcmToken, \DateTimeInterface $refreshTokenExpiresAt): AuthInfoDTO
    {
        $refresh = $this->generateRefreshToken();

        $device = Device::create([
            'user_id' => $user->id,
            'identifier' => Str::random(60),
            'refresh_token' => hash('sha256', $refresh->refresh_token.$refresh->salt),
            'salt' => $refresh->salt,
            'token_expires_at' => $refreshTokenExpiresAt,
            'fcm_token' => $fcmToken ?? null,
        ]);

        return new AuthInfoDTO(
            access_token: $this->createAccessTokenForDevice($user, $device, $tokenName),
            refresh_token: $refresh->refresh_token,
            device: $device->identifier
        );
    }

    public function createAccessTokenForDevice(User $user, Device $device, TokenNameEnum $tokenName = TokenNameEnum::LOGIN): string
    {
        $token = $user->createToken(
            $tokenName->value,
            ['*'],
            now()->addMinutes(config('sanctum.expiration'))
        );

        $token->accessToken->forceFill(['device_id' => $device->id])->save();

        return $token->plainTextToken;
    }

    public function refreshAccessToken(RefreshDTO $refreshDTO): ?AuthInfoDTO
    {
        $device = Device::where('identifier', $refreshDTO->device)
            ->firstOrFail();

        $refreshToken = $refreshDTO->refresh_token;
        $hashed_refresh_token = hash('sha256', $refreshToken.$device->salt);

        $user = $device->user;

        if (! hash_equals($device->refresh_token, $hashed_refresh_token) || now()->greaterThan($device->token_expires_at) || ! $user) {
            DB::table('personal_access_tokens')->where('device_id', $device->id)->delete();

            $device->delete();

            return null;
        }


        $user->tokens()->where('device_id', $device->id)->delete();

        $new_refresh = $this->generateRefreshToken();

        $device->update([
            'refresh_token' => hash('sha256', $new_refresh->refresh_token.$new_refresh->salt),
            'salt' => $new_refresh->salt,
        ]);

        return new AuthInfoDTO(
            $this->createAccessTokenForDevice($user, $device, TokenNameEnum::REFRESH),
            $new_refresh->refresh_token
        );
    }

    private function generateRefreshToken(): NewRefreshDTO
    {
        return new NewRefreshDTO(
            refresh_token: Str::random(64),
            salt: Str::random(32)
        );
    }
}
