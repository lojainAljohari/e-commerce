<?php

namespace App\Http\Requests\V1\User;

use App\Http\Requests\UnifiedApiRequest;

class ShowOrderRequest extends UnifiedApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('order')) ?? false;
    }
}
