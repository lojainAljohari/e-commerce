<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource->access_token,
            'refresh_token' => $this->resource->refresh_token,
            'device' => $this->whenNotNull($this->resource->device),
        ];
    }
}
