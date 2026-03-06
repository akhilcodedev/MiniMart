<?php

namespace Modules\Customer\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $customer;
    public function __construct($customer)
    {
        $this->customer = $customer;
        //dd($customer['name'] ?? 'test');
    }

    /**
     * Build the message.
     */
    public function build()
    {

        return $this->subject('Welcome to Our Store')
            ->view('customer::email.send_email_to_customer') // Reference module-specific view
            ->with(['customer' => $this->customer]);
    }
    //        Mail::to($customer->email)->send(new CustomerCreatedMail($customer->toArray()));
}
