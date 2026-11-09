<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderItemResource extends JsonResource
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
