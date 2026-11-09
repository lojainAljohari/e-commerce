<?php

namespace App\Http\Controllers\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\V1\Admin\AdminOrderResource;
use App\Http\Resources\V1\Admin\AdminOrderStatusResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Warehouse\WarehouseClient;
use App\Services\Warehouse\WarehouseUnavailableException;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    public function statuses(): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        return $this->succeed(__('Order statuses fetched successfully'), AdminOrderStatusResource::collection(OrderStatus::cases()));
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);
        $orders = Order::with('items.product.images')->latest()->paginate(min((int) $request->integer('per_page', 20), 100));

        return $this->succeed(__('Orders fetched successfully'), ['orders' => AdminOrderResource::collection($orders), 'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()]]);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return $this->succeed(__('Order fetched successfully'), new AdminOrderResource($order->load('items.product.images')));
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        WarehouseClient $warehouse
    ): JsonResponse {

        $newStatus = OrderStatus::from($request->validated('status'));


        if ($order->status === $newStatus) {
            return $this->succeed(__('Order status updated successfully'), []);
        }


        if (
            $order->status === OrderStatus::DELIVERED &&
            $newStatus === OrderStatus::CANCELLED
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A delivered order cannot be cancelled.',
            ], 409);
        }


        if ($order->status === OrderStatus::CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'A cancelled order cannot be reopened.',
            ], 409);
        }


        if ($newStatus === OrderStatus::CANCELLED) {

            if (!$order->reservation_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has no reservation ID.',
                ], 409);
            }

            try {
                $released = $warehouse->release($order->reservation_id);

                if (!$released) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not release the reserved stock.',
                    ], 409);
                }

            } catch (WarehouseUnavailableException $e) {

                Log::error('Warehouse release failed', [
                    'order_id' => $order->id,
                    'reservation_id' => $order->reservation_id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse service is unavailable. Please try again later.',
                ], 503);
            }
        }


        $order->update([
            'status' => $newStatus,
        ]);

        return $this->succeed(__('Order status updated successfully'), []);
    }
}
