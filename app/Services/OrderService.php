<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $order = Order::whereOrderId($data['order_id'])->first();

        dd($order);

        // Ignore duplicates based on order_id
        if ($order) {
            return;
        }

        // Find the merchant
        $merchant = Merchant::whereDomain($data['merchant_domain'])->first();

        if (!$merchant) {
            return;
        }

        // Find or create the affiliate
        $affiliate = $this->affiliateService->findOrCreateAffiliate($merchant, $data['customer_email'], $data['customer_name'],0);

        // Create the order
        $order = Order::create([
            'order_id' => $data['order_id'],
            'subtotal_price' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'discount_code' => $data['discount_code'],
        ]);

        // Log commissions
        $this->logCommissions($order);
    }

    protected function logCommissions(Order $order)
    {
        $merchantCommissionRate = $order->merchant->commission_rate;
        $affiliateCommission = $order->subtotal_price * $merchantCommissionRate;

        // Update the order with the commission value
        $order->update(['commission' => $affiliateCommission]);

        // Log the commission for the affiliate
        // $order->affiliate->commissions()->create([
        //     'order_id' => $order->id,
        //     'amount' => $affiliateCommission,
        // ]);
    }

}
