<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreProductImagesRequest;
use App\Http\Requests\V1\Admin\StoreProductRequest;
use App\Http\Requests\V1\Admin\UpdateProductRequest;
use App\Http\Resources\V1\Admin\AdminProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\V1\Admin\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminProductController extends Controller
{
    public function index(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);
        $query = $category->products()->with(['images'])->latest();
        $name = $request->string('name')->trim()->toString();

        if ($name !== '') {
            $query->where('name_'.app()->getLocale(), 'like', '%'.$name.'%');
        }

        $products = $query->paginate(5);

        return $this->succeed(__('Category products fetched successfully'), [
            'products' => AdminProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request, ProductImageService $imageService): JsonResponse
    {
        Gate::authorize('create', Product::class);
        $product = Product::create($request->safe()->except('images'));
        if ($request->hasFile('images')) {
            $imageService->store($product, $request->file('images'));
        }

        return $this->succeed(__('Product created successfully'), new AdminProductResource($product->load(['category', 'images'])), 201);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        return $this->succeed(__('Product fetched successfully'), new AdminProductResource($product->load(['images'])));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);
        $product->update($request->validated());

        return $this->succeed(__('Product updated successfully'), new AdminProductResource($product->refresh()->load(['category', 'images'])));
    }

    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return $this->succeed(__('Product deleted successfully'), [], 204);
    }

    public function storeImages(Product $product, StoreProductImagesRequest $request, ProductImageService $imageService): JsonResponse
    {

        Gate::authorize('update', $product);
        if ($product->images()->count() + count($request->file('images')) > 5) {
            return $this->failed(__('A product can have a maximum of 5 images.'), 422);
        }
        $imageService->store($product, $request->file('images'));

        return $this->succeed(__('Product images added successfully'), [], 201);
    }

    public function destroyImage(Product $product, ProductImage $productImage, ProductImageService $imageService): JsonResponse
    {
        abort_unless($productImage->product_id === $product->id, 404);
        Gate::authorize('delete', $productImage);
        $imageService->delete($productImage);

        return $this->succeed(__('Product image deleted successfully'), [], 204);
    }
}
