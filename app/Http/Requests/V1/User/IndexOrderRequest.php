<?php

namespace App\Http\Requests\V1\User;

use App\Http\Requests\UnifiedApiRequest;
use App\Models\Order;

class IndexOrderRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Order::class) ?? false;
    }

    public function rules(): array
    {
        return ['per_page' => ['nullable', 'integer', 'min:1', 'max:100']];
    }
}
