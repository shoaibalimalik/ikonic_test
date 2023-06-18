<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Merchant;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        try {
            $merchant = Merchant::find($this->order->merchant_id);
            // Perform the payout using the API service
            $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

            // Update the order status to "paid" if the payout is successful
            $this->order->update(['payout_status' => Order::STATUS_PAID]);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log the error)

            // Update the order status to "unpaid" if the payout fails
            $this->order->update(['payout_status' => Order::STATUS_UNPAID]);
            throw new \RuntimeException('an error occured');
        }
    }
}
