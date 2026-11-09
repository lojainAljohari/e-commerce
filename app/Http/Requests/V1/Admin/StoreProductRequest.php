<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\UnifiedApiRequest;
use App\Models\Product;

class StoreProductRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    public function rules(): array
    {
        return ['category_id' => ['required', 'integer', 'exists:categories,id'], 'name_en' => ['required', 'string', 'max:255'], 'name_ar' => ['required', 'string', 'max:255'], 'description_en' => ['required', 'string'], 'description_ar' => ['required', 'string'], 'images' => ['nullable', 'array', 'max:5'], 'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }
}
