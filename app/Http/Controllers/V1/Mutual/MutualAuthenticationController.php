<?php

namespace App\Http\Controllers\V1\Mutual;

use App\DTOs\V1\RefreshDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Mutual\RefreshTokenRequest;
use App\Http\Resources\V1\User\UserAuthInfoResource;
use App\Services\Mutual\MutualAuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MutualAuthenticationController extends Controller
{
    public function __construct(public MutualAuthenticationService $auth_service){}

     public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $refreshDTO = RefreshDTO::fromRequest($request);
        
        $result = $this->auth_service->refresh($refreshDTO);

        if(!$result['status']){
            return $this->failed($result['data']);
        }

        return $this->succeed(__('New access and refresh tokens are generated successfully'),
                              new UserAuthInfoResource($result['data']), 201);
    }

    public function logout(): JsonResponse
    {
        $this->auth_service->logout();

        return $this->succeed(__('Logged out successfully'));
    }

    public function logoutOtherDevices(): JsonResponse
    {
        $this->auth_service->logoutOtherDevices();

        return $this->succeed(__('Logged out from other devices successfully'));
    }

    public function logoutAllDevices(): JsonResponse
    {
        $this->auth_service->logoutAllDevices();

        return $this->succeed(__('Logged out from all devices successfully'));
    }

}
