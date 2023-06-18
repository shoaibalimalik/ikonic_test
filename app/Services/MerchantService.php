<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ]);

        $merchant = $user->merchant()->create([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
        ]);

        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        $user = User::whereEmail($email)->first();

        return ($user) ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
            ->where('payout_status', Order::STATUS_UNPAID)
            ->get();

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }

    public function getOrdersCount(string $fromDate, string $toDate): int
    {
        return Order::whereBetween('created_at', [$fromDate, $toDate])
            ->count();
    }

    public function getUnpaidCommissions(string $fromDate, string $toDate): float
    {
        return Order::where('payout_status', Order::STATUS_UNPAID)
            ->whereNotNull('affiliate_id')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('commission_owed');
    }

    public function getOrderSubtotalsSum(string $fromDate, string $toDate): float
    {
        return Order::whereBetween('created_at', [$fromDate, $toDate])
            ->sum('subtotal');
    }
}