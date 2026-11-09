<?php

namespace App\Http\Requests\V1\Mutual;

use App\Http\Requests\UnifiedApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RefreshTokenRequest extends UnifiedApiRequest
{
    public function prepareForValidation()
    {
        $this->merge([
            'device' => $this->header('device'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string', 'max:255'],
            'device' => ['required', 'string', 'max:255', Rule::exists('devices', 'identifier')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('device')) {
                    $validator->errors()->forget('device');
                    $validator->errors()->add('device', __('Invalid device'));

                    return;
                }
            },
        ];
    }
}
