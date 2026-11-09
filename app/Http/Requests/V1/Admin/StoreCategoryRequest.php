<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\UnifiedApiRequest;
use App\Models\Category;

class StoreCategoryRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    public function rules(): array
    {
        return ['name_en' => ['required', 'string', 'max:255'], 'name_ar' => ['required', 'string', 'max:255']];
    }
}
