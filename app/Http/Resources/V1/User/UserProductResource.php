<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return ['id' => $this->id, 'name' => $this->{'name_'.$locale}, 'description' => $this->{'description_'.$locale}, 'category' => new UserCategoryResource($this->whenLoaded('category')),'available_quantity' => $this->resource->getAttribute('available_quantity')??null, 'images' => UserProductImageResource::collection($this->whenLoaded('images'))];
    }
}
