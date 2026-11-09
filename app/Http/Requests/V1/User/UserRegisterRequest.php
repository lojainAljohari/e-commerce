<?php

namespace App\Http\Requests\V1\User;

use App\Http\Requests\UnifiedApiRequest;
use Illuminate\Validation\Rules\Password;

class UserRegisterRequest extends UnifiedApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)
                ->max(70)
                ->letters()
                ->mixedCase()
                ->symbols()
                ->numbers()],
        ];
    }
}
