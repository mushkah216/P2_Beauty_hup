<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreProductRequest;
use App\Http\Requests\Warehouse\UpdateProductRequest;
use App\Services\Warehouse\WarehouseProductService;
use Illuminate\Http\Request;

class WarehouseProductController extends Controller
{
    public WarehouseProductService $productService;

    public function __construct(WarehouseProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        return $this->productService->index($request);
    }

    public function store(StoreProductRequest $request)
    {
        return $this->productService->store($request, $request->validated());
    }

    public function update(UpdateProductRequest $request, $product)
    {
        return $this->productService->update($request, $product, $request->validated());
    }

    public function destroy($product)
    {
        return $this->productService->destroy($product);
    }
}