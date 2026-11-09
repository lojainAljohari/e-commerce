<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserCategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('products')->orderBy('id');
        $name = $request->string('name')->trim()->toString();

        if ($name !== '') {
            $query->where('name_'.app()->getLocale(), 'like', '%'.$name.'%');
        }

        $categories = $query->paginate(5);

        return $this->succeed(__('Categories fetched successfully'), [
            'categories' => UserCategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'total' => $categories->total(),
            ],
        ]);
    }
}
