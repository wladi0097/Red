<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\OrderStatus;
use App\Services\RedProviderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    private RedProviderService $redProviderService;
    public function __construct(RedProviderService $redProviderService)
    {
        $this->redProviderService = $redProviderService;
    }

    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('sort_by')) {
            $query->orderBy($request->sort_by, $request->get('sort_order', 'asc'));
        }

        return response()->json($query->get());
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $redOrder = $this->redProviderService->createOrder($validated['type']);

        if (!$redOrder || !isset($redOrder['id'])) {
            return response()->json(['error' => 'There was an error creating the order with the provider.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $order = Order::create([
            ...$validated,
            'red_id' => $redOrder['id'],
            'status' => OrderStatus::ORDERED,
        ]);

        return response()->json($order, Response::HTTP_CREATED);
    }


    public function show(Order $order)
    {
        return response()->json($order, Response::HTTP_OK);
    }

    public function destroy(Order $order)
    {
        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Only completed orders can be deleted.'], 400);
        }

        $this->redProviderService->deleteOrder($order->id);
        $order->delete();
        return response()->noContent();
    }
}
