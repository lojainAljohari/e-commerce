<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\UnifiedApiRequest;

class UpdateProductRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product')) ?? false;
    }

    public function rules(): array
    {
        return ['name_en' => ['required', 'string', 'max:255'], 'name_ar' => ['required', 'string', 'max:255'], 'description_en' => ['required', 'string'], 'description_ar' => ['required', 'string']];
    }
}
