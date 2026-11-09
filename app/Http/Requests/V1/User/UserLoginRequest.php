<?php

namespace App\Http\Requests\V1\User;

use App\Enums\RoleEnum;
use App\Http\Requests\UnifiedApiRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserLoginRequest extends UnifiedApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember_me' => ['nullable', 'boolean'],
            'fcm_token' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.max' => __('Invalid credentials. Check your email and password and try again.'),
            'email.max' => __('Invalid credentials. Check your email and password and try again.'),
        ];
    }

    public function check_credentials(): ?JsonResponse
    {
        $user = User::query()
            ->where('email', $this->string('email')->toString())
            ->whereRelation('roles', 'name', RoleEnum::USER->value)
            ->first();

        if (! $user ||
        ! Hash::check($this->password, $user->password)) {
            return $this->failed(__('Invalid credentials. Check your email and password and try again.'), 401);
        }

        return null;
    }
}
