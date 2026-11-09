<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreCategoryRequest;
use App\Http\Requests\V1\Admin\UpdateCategoryRequest;
use App\Http\Resources\V1\Admin\AdminCategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Category::class);
        $query = Category::query()->withCount('products');
        $name = $request->string('name')->trim()->toString();

        if ($name !== '') {
            $query->where('name_'.app()->getLocale(), 'like', '%'.$name.'%');
        }

        $categories = $query->paginate(5);

        return $this->succeed(__('Categories fetched successfully'), [
            'categories' => AdminCategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Category::class);
        $category = Category::create($request->validated());

        return $this->succeed(__('Category created successfully'), new AdminCategoryResource($category), 201);
    }

    public function show(Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return $this->succeed(__('Category fetched successfully'), new AdminCategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());

        return $this->succeed(__('Category updated successfully'), new AdminCategoryResource($category->refresh()));
    }

    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);
        $category->delete();

        return $this->succeed(__('Category deleted successfully'), [], 204);
    }
}
