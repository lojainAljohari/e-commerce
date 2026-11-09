<?php

namespace App\DTOs\V1;

readonly class AuthInfoDTO
{
    public function __construct(
        public string $access_token,
        public string $refresh_token,
        public ?string $device = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'device' => $this->device,
        ], fn ($value) => ! is_null($value));
    }
}
