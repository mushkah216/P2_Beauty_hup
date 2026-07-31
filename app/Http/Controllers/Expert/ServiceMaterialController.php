<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\SyncServiceMaterialsRequest;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceMaterial;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceMaterialController extends Controller
{
    use ApiResponseTrait;

    private function findServiceOrFail(Request $request, int|string $id): Service
    {
        $service = Service::query()
            ->where('provider_type', Product::PROVIDER_EXPERT)
            ->where('provider_id', $request->user()->getKey())
            ->find($id);

        if ($service === null) {
            throw new HttpResponseException(
                $this->sendError('الخدمة غير موجودة أو لا تتبع حسابك.', 404)
            );
        }

        return $service;
    }

    /** GET expert/services/{service}/materials */
    public function index(Request $request, int|string $service): JsonResponse
    {
        $serviceModel = $this->findServiceOrFail($request, $service);

        $materials = ServiceMaterial::query()
            ->with('product:id,name,sku,stock_quantity,min_stock_threshold')
            ->where('service_id', $serviceModel->getKey())
            ->get()
            ->map(fn (ServiceMaterial $m) => [
                'product_id'           => $m->product_id,
                'product_name'         => $m->product?->name,
                'sku'                  => $m->product?->sku,
                'quantity_per_session' => (float) $m->quantity_per_session,
                'unit'                 => $m->unit,
                'stock_quantity'       => (float) ($m->product?->stock_quantity ?? 0),
                // الكمية الموجودة تكفي كم جلسة من هالخدمة
                'covers_sessions'      => $m->quantity_per_session > 0
                    ? (int) floor((float) ($m->product?->stock_quantity ?? 0) / (float) $m->quantity_per_session)
                    : null,
            ]);

        return $this->sendResponse([
            'service_id'   => $serviceModel->getKey(),
            'service_name' => $serviceModel->name,
            'materials'    => $materials,
        ], 'تم جلب مواد الخدمة.');
    }

    /**
     * POST expert/services/{service}/materials
     * منبعت القائمة كاملة وهي تستبدل القديمة (sync) — أسهل ع الفرونت من add/remove.
     */
    public function sync(SyncServiceMaterialsRequest $request, int|string $service): JsonResponse
    {
        $serviceModel = $this->findServiceOrFail($request, $service);
        $expertId     = $request->user()->getKey();
        $materials    = $request->input('materials', []);

        $productIds = collect($materials)->pluck('product_id')->unique();

        // كل المواد لازم تكون من مخزون نفس الخبير
        $ownedCount = Product::query()
            ->forProvider(Product::PROVIDER_EXPERT, $expertId)
            ->whereIn('id', $productIds)
            ->count();

        if ($ownedCount !== $productIds->count()) {
            return $this->sendError('في مواد ما تتبع مخزونك.', 422);
        }

        DB::transaction(function () use ($serviceModel, $materials) {
            ServiceMaterial::query()->where('service_id', $serviceModel->getKey())->delete();

            foreach ($materials as $material) {
                ServiceMaterial::create([
                    'service_id'           => $serviceModel->getKey(),
                    'product_id'           => $material['product_id'],
                    'quantity_per_session' => $material['quantity_per_session'],
                    'unit'                 => $material['unit'] ?? null,
                ]);
            }
        });

        return $this->index($request, $service);
    }
}
