<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\RejectOrderRequest;
use App\Http\Requests\Warehouse\UpdateOrderStatusRequest;
use App\Services\Warehouse\WarehouseOrderService;
use Illuminate\Http\Request;

class WarehouseOrderController extends Controller
{
    public WarehouseOrderService $orderService;

    public function __construct(WarehouseOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        return $this->orderService->index($request);
    }

    public function show($order)
    {
        return $this->orderService->show($order);
    }

    public function accept($order)
    {
        return $this->orderService->accept($order);
    }

    public function reject(RejectOrderRequest $request, $order)
    {
        return $this->orderService->reject($order, $request->validated());
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $order)
    {
        return $this->orderService->updateStatus($order, $request->validated());
    }
}