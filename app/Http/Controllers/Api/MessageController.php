<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerAggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected CustomerAggregationService $customers,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->customers->hostThreadList($request->user()),
        ]);
    }
}
