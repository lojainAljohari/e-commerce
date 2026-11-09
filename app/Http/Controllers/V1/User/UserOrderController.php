<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\IndexOrderRequest;
use App\Http\Requests\V1\User\ShowOrderRequest;
use App\Http\Requests\V1\User\StoreOrderRequest;
use App\Http\Resources\V1\User\UserOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\Warehouse\WarehouseClient;
use App\Services\Warehouse\WarehouseUnavailableException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UserOrderController extends Controller
{
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $orders = $request->user()->orders()->with('items.product.images')->latest()->paginate(10);

        return $this->succeed(__('Orders fetched successfully'), ['orders' => UserOrderResource::collection($orders), 'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()]]);
    }

    public function store(StoreOrderRequest $request, WarehouseClient $warehouse): JsonResponse
    {
        Gate::authorize('create', Order::class);

        $productIds = $request->validated('product_ids');
        $quantities = array_count_values($productIds);
        $reservationId = (string) Str::uuid();

        try {
            $reservation = $warehouse->reserve($reservationId, $quantities);
        } catch (WarehouseUnavailableException $e) {
            Log::warning('Warehouse unavailable while reserving', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('The warehouse service is not responding right now, please try again later'),
            ], 503);
        }

        if (!$reservation['success']) {
            return response()->json(['success' => false, 'message' => $reservation['message']], 409);
        }

        try {
            DB::transaction(function () use ($request, $productIds, $reservationId): Order {
                $order = $request->user()->orders()->create([
                    'status' => 'pending',
                    'reservation_id' => $reservationId,
                ]);

                $order->items()->createMany(array_map(
                    fn(int $productId): array => ['product_id' => $productId],
                    $productIds
                ));

                return $order;
            });
        } catch (Throwable $e) {
            try {
                $warehouse->release($reservationId);
            } catch (WarehouseUnavailableException) {
                Log::error('Could not release reservation', ['reservation_id' => $reservationId]);
            }

            throw $e;
        }

        return $this->succeed(__('Order created successfully'), [], 201);
    }

    public function show(ShowOrderRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return $this->succeed(__('Order fetched successfully'), new UserOrderResource($order->load('items.product.images')));
    }
}
