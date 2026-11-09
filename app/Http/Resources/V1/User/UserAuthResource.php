<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),
            'access_token' => $this->when(isset($this->access_token), $this->access_token),
            'refresh_token' => $this->when(isset($this->refresh_token), $this->refresh_token),
        ];
    }
}
