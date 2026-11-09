<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdminProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'url' => Storage::disk('public')->url($this->path)];
    }
}
