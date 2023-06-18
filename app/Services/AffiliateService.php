<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        // Create the affiliate

        if(User::where('email',$email)->exists()){
            throw new AffiliateCreateException('Failed to create affiliate');
        }

        $discount = $this->apiService->createDiscountCode($merchant);

        $affiliate = Affiliate::create([
            'user_id' => $merchant->user_id,
            'merchant_id' => $merchant->id,
            'email' => $email,
            'name' => $name,
            'commission_rate' => $commissionRate,
            'discount_code' => $discount['code'],
        ]);

        // Check if the affiliate creation was successful
        if (!$affiliate) {
            throw new AffiliateCreateException('Failed to create affiliate');
        }



        // Send email notification to the affiliate
        Mail::to($affiliate->email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }

    public function findOrCreateAffiliate(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        return Affiliate::firstOrCreate(
            ['merchant_id' => $merchant->id, 'user_id' => $merchant->user->id, 'email' => $email],
            ['name' => $name, 'commission_rate' => $commissionRate]
        );
    }
}
