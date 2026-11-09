<?php

namespace App\Http\Requests;

use App\Traits\JsonResponseTrait;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class UnifiedApiRequest extends FormRequest
{
    use JsonResponseTrait;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool|Response
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->failed($validator->errors()->first())
        );
    }
}
