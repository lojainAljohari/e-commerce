<?php

namespace App\DTOs\V1\User;

readonly class UserNormalAuthInfoDTO
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
            remember_me: isset($data['remember_me']) ? $data['remember_me'] : null
        );
    }
}
