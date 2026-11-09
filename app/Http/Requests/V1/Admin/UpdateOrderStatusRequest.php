<?php

namespace App\Http\Requests\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Requests\UnifiedApiRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderStatusRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('order')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', new Enum(OrderStatus::class)]];
    }
}
