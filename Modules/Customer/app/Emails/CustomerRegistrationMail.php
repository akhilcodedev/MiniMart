<?php

namespace Modules\Customer\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;


    public $customer;

    /**
     * Create a new message instance.
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
         return $this->subject('Welcome to Our Platform')
                            ->view('emails.register-email')
                            ->with([
                                'customerName' => $this->customer->first_name . ' ' . $this->customer->last_name,
                            ]);
    }
}
