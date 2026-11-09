<?php

namespace App\Http\Requests\V1\User;

use App\Http\Requests\UnifiedApiRequest;
use App\Models\Order;

class StoreOrderRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    public function rules(): array
    {
        return ['product_ids' => ['required', 'array', 'min:1'], 'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id']];
    }
}
