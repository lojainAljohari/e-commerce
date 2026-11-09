<?php

namespace App\DTOs\V1;

readonly class NewRefreshDTO
{
    public function __construct(
        public string $refresh_token,
        public string $salt
    ) {}
}
