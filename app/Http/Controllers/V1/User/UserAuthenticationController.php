<?php

namespace App\Http\Controllers\V1\User;

use App\DTOs\V1\User\UserNormalAuthInfoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\UserLoginRequest;
use App\Http\Requests\V1\User\UserRegisterRequest;
use App\Http\Requests\V1\User\UserVerifyEmailRequest;
use App\Http\Resources\V1\User\UserAuthInfoResource;
use App\Services\V1\User\UserAuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAuthenticationController extends Controller
{
    public function __construct(
        private readonly UserAuthenticationService $userAuthenticationService
    ) {}

    public function register(UserRegisterRequest $request): JsonResponse
    {
        $dto = UserNormalAuthInfoDTO::fromArray($request->validated());
        $data = $this->userAuthenticationService->register($dto);

        return $this->succeed(__('User registered successfully, and a verification code is sent to your email'), new UserAuthInfoResource($data), 201);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        if ($response = $request->check_credentials()) {
            return $response;
        }
        $dto = UserNormalAuthInfoDTO::fromArray([
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'remember_me' => $request->validated('remember_me') ?? false,
        ]);

        $result = $this->userAuthenticationService->login($dto);

        return $this->succeed($result['message'], $result['data']->toArray(), 201);
    }

    public function verify_email(UserVerifyEmailRequest $request): JsonResponse
    {

        $verified = $this->userAuthenticationService->verify_email($request->user(), $request->validated('code'));

        if (! $verified) {
            return $this->failed(__('Invalid or expired verification code.'), 422);
        }

        return $this->succeed(__('Email verified successfully.'));
    }

    public function resend_verification_code(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->userAuthenticationService->resend_verification_code($user);

        return $this->succeed(__('Verification code resent successfully.'));
    }
}
