<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        protected MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $fromDate = Carbon::parse($request->input('from'))->startOfDay();
        $toDate = Carbon::parse($request->input('to'))->endOfDay();

        $ordersCount = $this->merchantService->getOrdersCount($fromDate, $toDate);
        $commissionOwed = $this->merchantService->getUnpaidCommissions($fromDate, $toDate);
        $revenue = $this->merchantService->getOrderSubtotalsSum($fromDate, $toDate);

        return response()->json([
            'count' => $ordersCount,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}
