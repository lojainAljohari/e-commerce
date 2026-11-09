<?php

namespace App\DTOs\V1;

use App\Http\Requests\V1\Mutual\RefreshTokenRequest;

readonly class RefreshDTO
{
    public function __construct(
        public string $refresh_token,
        public string $device
    ) {}

    public static function fromRequest(RefreshTokenRequest $request): self
    {
        return new self(
            refresh_token: $request->validated('refresh_token'),
            device: $request->validated('device')
        );
    }
}
