<?php

namespace App\Services\V1\User;

use App\Actions\SendEmailVerificationCodeAction;
use App\DTOs\V1\AuthInfoDTO;
use App\DTOs\V1\User\UserNormalAuthInfoDTO;
use App\Enums\RoleEnum;
use App\Enums\TokenNameEnum;
use App\Models\User;
use App\Services\Mutual\TokenDeviceManagementService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserAuthenticationService
{
    public function __construct(protected SendEmailVerificationCodeAction $sendEmailVerificationCodeAction,
        protected TokenDeviceManagementService $token_service) {}

    public function register(UserNormalAuthInfoDTO $dto): AuthInfoDTO
    {
        return DB::transaction(function () use ($dto) {
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => bcrypt($dto->password),
            ]);

            $user->assignRole(RoleEnum::USER->value);

            $this->sendEmailVerificationCodeAction->execute($user);

            $data = $this->token_service->issueTokenPair(
                $user,
                TokenNameEnum::REGISTER,
                null,
                now()->addDays(config('app.refresh_expiration_with_remember_me'))
            );

            return $data;
        });
    }

    public function login(UserNormalAuthInfoDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $user = User::query()
                ->where('email', $dto->email)
                ->whereRelation('roles', 'name', RoleEnum::USER->value)
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

            if (! $user->email_verified_at) {
                $message = __('Please verify your email to complete the login process. A verification code has been sent to your email.');

                $this->sendEmailVerificationCodeAction->execute($user);
            }

            return [
                'message' => $message,
                'data' => $data,
            ];
        });
    }

    /**
     * Verify email with the provided code.
     */
    public function verify_email(User $user, string $code): bool
    {
        $cached_code = Cache::get("verification_code_{$user->id}");

        if ($cached_code && $cached_code === $code) {
            $user->update([
                'email_verified_at' => now(),
            ]);

            Cache::forget("verification_code_{$user->id}");

            return true;
        }

        return false;
    }

    /**
     * Resend verification code to user.
     */
    public function resend_verification_code(User $user): void
    {
        $this->sendEmailVerificationCodeAction->execute($user);
    }
}
