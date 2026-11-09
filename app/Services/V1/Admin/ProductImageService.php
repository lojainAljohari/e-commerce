<?php

namespace App\Services\V1\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    /** @param array<int, UploadedFile> $images */
    public function store(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $resource = imagecreatefromstring($image->get());
            if ($resource === false) {
                continue;
            }
            $path = 'products/'.$product->id.'/'.Str::uuid().'.webp';
            ob_start();
            imagewebp($resource, null, 85);
            $contents = ob_get_clean();
            imagedestroy($resource);
            Storage::disk('public')->put($path, $contents);
            $product->images()->create(['path' => $path]);
        }
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }
}
