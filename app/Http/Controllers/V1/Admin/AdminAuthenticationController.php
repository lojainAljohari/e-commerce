<?php

namespace App\Http\Controllers\V1\Admin;

use App\DTOs\V1\Admin\AdminNormalAuthInfoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\AdminLoginRequest;
use App\Services\V1\Admin\AdminAuthenticationService;
use Illuminate\Http\JsonResponse;

class AdminAuthenticationController extends Controller
{
    public function __construct(
        private readonly AdminAuthenticationService $adminAuthenticationService
    ) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {

        if ($response = $request->check_credentials()) {
            return $response;
        }
        $dto = AdminNormalAuthInfoDTO::fromArray([
            'name' => $request->validated('name') ?? '',
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'remember_me' => $request->validated('remember_me') ?? false,
        ]);

        $result = $this->adminAuthenticationService->login($dto);

        return $this->succeed($result['message'], $result['data']->toArray(), 201);
    }
}
