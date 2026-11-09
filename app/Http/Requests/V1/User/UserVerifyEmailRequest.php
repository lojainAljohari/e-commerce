<?php

namespace App\Http\Requests\V1\User;

use App\Http\Requests\UnifiedApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UserVerifyEmailRequest extends UnifiedApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
