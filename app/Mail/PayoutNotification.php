<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutNotification extends Mailable
{
    use Queueable, SerializesModels;

    public float $amount;

    /**
     * Create a new message instance.
     *
     * @param float $amount
     */
    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Payout Notification')
            ->view('emails.payout-notification')
            ->with([
                'amount' => $this->amount,
            ]);
    }
}