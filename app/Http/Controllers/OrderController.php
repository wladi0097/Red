<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\OrderStatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
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
        $order = Order::create([
            ...$request->validated(),
            'status' => 'ordered',
        ]);

        // TODO implement red service

        return response()->json($order, Response::HTTP_CREATED);
    }


    public function show(Order $order)
    {
        return response()->json($order, Response::HTTP_OK);
    }

    public function destroy(Order $order)
    {
        if ($order->status !== OrderStatus::COMPLETED) {
            return response()->json(['error' => 'Only completed orders can be deleted.'], 400);
        }

        $order->delete();
        return response()->noContent();
    }
}
