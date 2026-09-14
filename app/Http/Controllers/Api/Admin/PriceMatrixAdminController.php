<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingPricingFactor;
use App\Models\PricingMatrix;
use App\Services\PriceMatrixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceMatrixAdminController extends Controller
{
    /**
     * Column order shown in the grid; any other type_key present in the
     * pricing_matrices table is appended after these, sorted.
     */
    private const COLUMN_ORDER = ['Studio', '1BR', '2BR+1WC', '2BR+2WC', '3BR+2WC', '4BR+2WC', '4BR+3WC'];

    public function __construct(
        private readonly PriceMatrixService $priceMatrixService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $columns = $this->columns();

        $buildings = Building::query()
            ->with('district')
            ->where('status', '!=', 'archived')
            ->whereNotNull('district_id')
            ->when($request->filled('district_id'), fn ($q) => $q->where('district_id', $request->integer('district_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', $search)->orWhere('short_name', 'like', $search);
                });
            })
            ->orderBy('name')
            ->get();

        $groups = $buildings->groupBy(fn (Building $building) => $building->district?->district_id);

        $districts = $groups->map(function ($buildingsInDistrict, $districtId) use ($columns) {
            $district = $buildingsInDistrict->first()->district;

            $rows = $buildingsInDistrict->map(function (Building $building) use ($columns) {
                $prices = [];
                foreach ($columns as $typeKey) {
                    $prices[$typeKey] = $this->priceMatrixService->calculateDefaultMatrixPrice($building, $typeKey);
                }

                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'short_name' => $building->short_name,
                    'prices' => $prices,
                ];
            })->values();

            $averages = [];
            foreach ($columns as $typeKey) {
                $values = $rows->pluck('prices.'.$typeKey)->filter();
                $averages[$typeKey] = $values->isEmpty() ? null : (int) round($values->avg());
            }

            return [
                'district_id' => $districtId,
                'district_code' => $district?->district_code,
                'district_name' => $district?->name,
                'label' => trim(($district?->district_code ?? '?').' – '.($district?->name ?? 'Unknown')),
                'buildings' => $rows,
                'averages' => $averages,
            ];
        })->values();

        $districts = $districts->sortBy(
            fn ($group) => $group['district_code'] ?? 'zzz',
            SORT_NATURAL,
        )->values();

        return response()->json([
            'data' => [
                'columns' => $columns,
                'standard_factors' => $this->priceMatrixService->getStandardFactors(),
                'districts' => $districts,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'changes' => ['required', 'array', 'min:1'],
            'changes.*.building_id' => ['required', 'integer', 'exists:buildings,id'],
            'changes.*.type_key' => ['required', 'string'],
            'changes.*.price_vnd' => ['required', 'integer', 'min:0'],
        ]);

        $affectedBuildingIds = collect();

        DB::transaction(function () use ($data, &$affectedBuildingIds) {
            foreach ($data['changes'] as $change) {
                BuildingPricingFactor::query()->updateOrCreate(
                    ['building_id' => $change['building_id'], 'type_key' => $change['type_key']],
                    ['price_override_vnd' => $change['price_vnd']],
                );
                $affectedBuildingIds->push($change['building_id']);
            }
        });

        $columns = $this->columns();
        $buildings = Building::query()->with('district')->whereIn('id', $affectedBuildingIds->unique())->get();

        $updated = $buildings->map(function (Building $building) use ($columns) {
            $prices = [];
            foreach ($columns as $typeKey) {
                $prices[$typeKey] = $this->priceMatrixService->calculateDefaultMatrixPrice($building, $typeKey);
            }

            return [
                'id' => $building->id,
                'district_id' => $building->district_id,
                'prices' => $prices,
            ];
        })->values();

        return response()->json(['data' => $updated, 'message' => 'Price matrix updated.']);
    }

    private function columns(): array
    {
        $existing = PricingMatrix::pluck('type_key')->all();
        $ordered = array_values(array_filter(self::COLUMN_ORDER, fn ($key) => in_array($key, $existing, true)));
        $extra = array_values(array_diff($existing, self::COLUMN_ORDER));
        sort($extra);

        return array_merge($ordered, $extra);
    }
}
