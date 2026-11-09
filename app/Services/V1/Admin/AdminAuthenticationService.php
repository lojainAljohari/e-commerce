<?php

namespace App\Services\V1\Admin;

use App\DTOs\V1\Admin\AdminNormalAuthInfoDTO;
use App\Enums\RoleEnum;
use App\Enums\TokenNameEnum;
use App\Models\User;
use App\Services\Mutual\TokenDeviceManagementService;
use Illuminate\Support\Facades\DB;

class AdminAuthenticationService
{
    public function __construct(
        protected TokenDeviceManagementService $token_service
    ) {}

    public function login(AdminNormalAuthInfoDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $user = User::query()
                ->where('email', $dto->email)
                ->whereRelation('roles', 'name', RoleEnum::ADMIN->value)
                ->firstOrFail();

            $data = $this->token_service->issueTokenPair(
                $user,
                TokenNameEnum::LOGIN,
                null,
                $dto->remember_me
                    ? now()->addDays(config('app.refresh_expiration_with_remember_me'))
                    : now()->addDays(config('app.refresh_expiration_WITHOUT_remember_me'))
            );

            $message = __('You have successfully logged in.');

            return [
                'message' => $message,
                'data' => $data,
            ];
        });
    }

    public function createAdminWithTokens(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt($data['password']),
                ]
            );

            if (! $user->hasRole(RoleEnum::ADMIN->value)) {
                $user->assignRole(RoleEnum::ADMIN->value);
            }

            $user->tokens()->delete();

            $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60));
            $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

            $user->access_token = $accessToken->plainTextToken;
            $user->refresh_token = $refreshToken->plainTextToken;

            return $user;
        });
    }
}
