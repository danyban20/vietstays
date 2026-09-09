<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = $this->baseQuery($request)->get();

        $rows = $customers->map(fn (Customer $c) => $this->transform($c));

        $filtered = $this->applyFilters($rows, $request);
        $filtered = $this->applySort($filtered, $request->string('sort', 'stays')->toString());

        $tab = $request->string('tab', 'all')->toString();
        $tabbed = $this->applyTab($filtered, $tab);

        return response()->json([
            'data' => $tabbed->values(),
            'meta' => [
                'tab_counts' => [
                    'all' => $filtered->count(),
                    'needs_action' => $filtered->where('needs_action', true)->count(),
                    'staying' => $filtered->where('status', 'staying')->count(),
                    'upcoming' => $filtered->where('status', 'upcoming')->count(),
                    'repeat' => $filtered->whereIn('segment', ['repeat', 'vip'])->count(),
                ],
                'total_value' => $rows->sum('total_revenue'),
                'new_this_month' => $customers->filter(
                    fn (Customer $c) => $c->created_at && $c->created_at->isCurrentMonth()
                )->count(),
                'repeat_count' => $rows->whereIn('segment', ['repeat', 'vip'])->count(),
                'countries' => $rows->pluck('country')->filter()->unique()->sort()->values(),
            ],
        ]);
    }

    public function show(Request $request, int $customer): JsonResponse
    {
        $model = Customer::query()->with(['bookings' => function ($q) {
            $q->with('apartment')->orderByDesc('check_in_date');
        }])->findOrFail($customer);

        $this->authorizeCustomer($request, $model);

        return response()->json(['data' => $this->transform($model, detailed: true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer = Customer::query()->create([
            'user_id' => $request->user()->legacy_wp_id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'data' => $this->transform($customer),
            'message' => $customer->email ? 'Customer added.' : "Customer added as Temporary #{$customer->id}.",
        ], 201);
    }

    public function export(Request $request, CustomerExportService $exportService): StreamedResponse
    {
        $rows = $this->applyFilters(
            $this->baseQuery($request)->get()->map(fn (Customer $c) => $this->transform($c)),
            $request,
        );

        return $exportService->streamXlsx($rows);
    }

    protected function baseQuery(Request $request)
    {
        $query = Customer::query()->with(['bookings' => function ($q) {
            $q->with('apartment');
        }]);

        if ($request->user()->isOperator() && ! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->legacy_wp_id);
        }

        return $query;
    }

    protected function applyFilters(Collection $rows, Request $request): Collection
    {
        if ($request->filled('search')) {
            $search = mb_strtolower($request->string('search'));
            $rows = $rows->filter(fn (array $r) => str_contains(mb_strtolower($r['name']), $search)
                || str_contains(mb_strtolower((string) $r['email']), $search)
                || str_contains(mb_strtolower((string) $r['country']), $search));
        }

        if ($request->filled('country')) {
            $country = $request->string('country')->toString();
            $rows = $rows->filter(fn (array $r) => $r['country'] === $country);
        }

        if ($request->filled('segment')) {
            $segment = $request->string('segment')->toString();
            $rows = $rows->filter(fn (array $r) => $r['segment'] === $segment);
        }

        if ($request->filled('min_stays')) {
            $minStays = $request->integer('min_stays');
            $rows = $rows->filter(fn (array $r) => $r['bookings_count'] >= $minStays);
        }

        if ($request->boolean('upcoming_only')) {
            $rows = $rows->filter(fn (array $r) => $r['next_stay'] !== null);
        }

        if ($request->filled('from')) {
            $from = $request->string('from')->toString();
            $rows = $rows->filter(fn (array $r) => $r['next_stay'] && $r['next_stay']['date'] >= $from);
        }

        if ($request->filled('to')) {
            $to = $request->string('to')->toString();
            $rows = $rows->filter(fn (array $r) => $r['next_stay'] && $r['next_stay']['date'] <= $to);
        }

        return $rows->values();
    }

    protected function applyTab(Collection $rows, string $tab): Collection
    {
        return match ($tab) {
            'needs_action' => $rows->where('needs_action', true)->values(),
            'staying' => $rows->where('status', 'staying')->values(),
            'upcoming' => $rows->where('status', 'upcoming')->values(),
            'repeat' => $rows->whereIn('segment', ['repeat', 'vip'])->values(),
            default => $rows,
        };
    }

    protected function applySort(Collection $rows, string $sort): Collection
    {
        return match ($sort) {
            'nights' => $rows->sortByDesc('nights_total')->values(),
            'value' => $rows->sortByDesc('total_revenue')->values(),
            'next_stay' => $rows->sortBy(fn (array $r) => $r['next_stay']['date'] ?? '9999-99-99')->values(),
            'name' => $rows->sortBy(fn (array $r) => mb_strtolower($r['name']))->values(),
            default => $rows->sortByDesc('bookings_count')->values(),
        };
    }

    protected function authorizeCustomer(Request $request, Customer $customer): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isOperator() && (int) $customer->user_id === (int) $user->legacy_wp_id) {
            return;
        }

        abort(403);
    }

    protected function transform(Customer $customer, bool $detailed = false): array
    {
        $today = Carbon::today();
        $bookings = ($customer->relationLoaded('bookings') ? $customer->bookings : collect())
            ->reject(fn ($b) => $b->status === 'cancelled');

        $active = $bookings->first(fn ($b) => $b->check_in_date <= $today && $b->check_out_date > $today);
        $upcoming = $bookings->filter(fn ($b) => $b->check_in_date > $today)->sortBy('check_in_date');
        $past = $bookings->filter(fn ($b) => $b->check_out_date <= $today);
        $nextUpcoming = $upcoming->first();

        $count = $bookings->count();
        $nightsTotal = $bookings->sum(fn ($b) => max(1, (int) $b->check_in_date->diffInDays($b->check_out_date)));
        $totalRevenue = $bookings->sum(fn ($b) => (float) $b->total);

        $lastCheckout = $past->max('check_out_date');
        $daysSinceCheckout = $lastCheckout ? $today->diffInDays($lastCheckout) : null;

        $segment = match (true) {
            $count >= 4 => 'vip',
            $count >= 2 => 'repeat',
            $count === 1 && ($active || $nextUpcoming || ($daysSinceCheckout !== null && $daysSinceCheckout <= 30)) => 'new',
            default => null,
        };

        $status = match (true) {
            (bool) $active => 'staying',
            (bool) $nextUpcoming => 'upcoming',
            $count > 0 => 'past',
            default => null,
        };

        $needsAction = $bookings->contains(fn ($b) => $b->status === 'pending')
            || ($nextUpcoming && $today->diffInDays($nextUpcoming->check_in_date, false) <= 3);

        $nextStayBooking = $active ?: $nextUpcoming;
        $nextStay = $nextStayBooking ? [
            'date' => $nextStayBooking->check_in_date->format('Y-m-d'),
            'date_label' => $nextStayBooking->check_in_date->format('M j'),
            'days_until' => $active ? 0 : max(0, (int) $today->diffInDays($nextStayBooking->check_in_date, false)),
            'ongoing' => (bool) $active,
            'apartment' => $nextStayBooking->apartment?->display_name ?: $nextStayBooking->apartment?->name,
            'apartment_code' => $nextStayBooking->apartment
                ? 'VS'.str_pad((string) $nextStayBooking->apartment->ID, 3, '0', STR_PAD_LEFT)
                : null,
        ] : null;

        $data = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'country' => $customer->country,
            'unregistered' => ! $customer->email,
            'segment' => $segment,
            'status' => $status,
            'needs_action' => (bool) $needsAction,
            'bookings_count' => $count,
            'nights_total' => $nightsTotal,
            'total_revenue' => $totalRevenue,
            'next_stay' => $nextStay,
            'last_stay' => $lastCheckout?->format('Y-m-d'),
            'created_at' => $customer->created_at?->toIso8601String(),
        ];

        if ($detailed) {
            $data['note'] = $customer->note;
            $data['bookings'] = $bookings->sortByDesc('check_in_date')->map(fn ($b) => [
                'id' => $b->ID,
                'apartment' => $b->apartment?->display_name ?: $b->apartment?->name,
                'check_in' => $b->check_in_date->format('Y-m-d'),
                'check_out' => $b->check_out_date->format('Y-m-d'),
                'status' => $b->status,
                'total' => (float) $b->total,
            ])->values();
        }

        return $data;
    }
}
