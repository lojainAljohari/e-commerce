<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\UnifiedApiRequest;
use App\Models\ProductImage;

class StoreProductImagesRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProductImage::class) ?? false;
    }

    public function rules(): array
    {
        return ['images' => ['required', 'array', 'min:1', 'max:5'], 'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }
}
