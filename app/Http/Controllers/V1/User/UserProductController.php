<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Warehouse\WarehouseClient;
use App\Services\Warehouse\WarehouseUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProductController extends Controller
{
    public function index(Request $request, Category $category, WarehouseClient $warehouse): JsonResponse
    {
        $query = $category->products()->with(['category', 'images'])->latest();
        $name = $request->string('name')->trim()->toString();

        if ($name !== '') {
            $query->where('name_'.app()->getLocale(), 'like', '%'.$name.'%');
        }

        $products = $query->paginate(5);

        $unavailable = false;
        $products->getCollection()->transform(function (Product $product) use ($warehouse, &$unavailable) {
            $quantity = null;

            if (! $unavailable) {
                try {
                    $quantity = $warehouse->getQuantity($product->id);
                } catch (WarehouseUnavailableException) {
                    $unavailable = true;
                }
            }

            $product->setAttribute('available_quantity', $quantity);

            return $product;
        });

        return $this->succeed(__('Category products fetched successfully'), [
            'products' => UserProductResource::collection($products),
            'stock_message' => $unavailable ? __('Stock information is temporarily unavailable') : null,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product, WarehouseClient $warehouse): JsonResponse
    {
        $data = (new UserProductResource($product->load(['category', 'images'])))->resolve();

        try {
            $data['available_quantity'] = $warehouse->getQuantity($product->id);
        } catch (WarehouseUnavailableException) {
            $data['available_quantity'] = null;
            $data['stock_message'] = __('Stock information is temporarily unavailable');
        }

        return $this->succeed(__('Product fetched successfully'), $data);
    }
}
