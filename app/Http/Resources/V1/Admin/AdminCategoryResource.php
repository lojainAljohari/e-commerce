<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->{'name_'.$locale},
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'products' => AdminProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
