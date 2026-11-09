<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->when($this->relationLoaded('product'), function (): array {
                $locale = app()->getLocale();

                return ['id' => $this->product->id, 'name' => $this->product->{'name_'.$locale}];
            }),
        ];
    }
}
