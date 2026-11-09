<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return ['id' => $this->id, 'name' => $this->{'name_'.$locale}, 'description' => $this->{'description_'.$locale}, 'category' => new AdminCategoryResource($this->whenLoaded('category')), 'images' => AdminProductImageResource::collection($this->whenLoaded('images'))];
    }
}
