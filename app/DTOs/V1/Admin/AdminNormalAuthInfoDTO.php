<?php

namespace App\DTOs\V1\Admin;

readonly class AdminNormalAuthInfoDTO
{
    public function __construct(
        public ?string $name,
        public string $email,
        public string $password,
        public ?bool $remember_me = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'],
            password: $data['password'],
            remember_me: isset($data['remember_me']) ? (bool) $data['remember_me'] : null
        );
    }
}
