<?php

namespace Modules\Cart\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Cart\Models\AbandonedCart;
use Modules\Cart\Models\Cart;
use Modules\Customer\Models\Customer;
use Modules\WebConfigurationManagement\Models\CoreConfigData;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;


class CheckAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cart:check-abandoned';
    protected $description = 'Check abandoned carts and send email notifications';

    public function handle()
    {
        Log::info("Starting abandoned cart email processing...");

        // Fetch configuration values
        $sendNotification  = (int) getConfigData('send_notification', 'cart.') ?? 0;
        $abandonedCartDays = (int) getConfigData('abandoned_cart_days', 'cart.') ?? 0;
        // Set number of mails to 3 for a total 60-second period (20s, 40s, 60s)
        $noOfMails         = (int) getConfigData('no_of_mails', 'cart.') ?? 3;

        if (!$sendNotification) {
            Log::warning('Abandoned cart notification is disabled.');
            $this->info("Abandoned cart notification is disabled.");
            return;
        }

        $cutoffDate = now()->subDays($abandonedCartDays);
        Log::info("Cutoff date for abandoned carts: {$cutoffDate}");

        // Fetch abandoned carts
        $abandonedCarts = Cart::whereNotNull('updated_at')
            ->where('updated_at', '<=', $cutoffDate)
            ->where('is_cart_active', 1) // Only fetch active carts
            ->get();

        if ($abandonedCarts->isEmpty()) {
            Log::info("No abandoned carts found for email processing.");
            return;
        }

        foreach ($abandonedCarts as $cart) {
            Log::info("Processing cart ID: {$cart->id}");

            // Save/update abandoned cart details with initial sent_email_count set to 0
            $customer = Customer::find($cart->customer_id);
            $abandonedCart = AbandonedCart::updateOrCreate(
                ['cart_id' => $cart->id],
                [
                    'store_id'         => null, // Session not available in console commands
                    'customer_id'      => $cart->customer_id,
                    'email'            => $customer ? $customer->email : $cart->email,
                    'is_active'        => $cart->is_cart_active,
                    'sent_email_count' => 0,
                ]
            );

            // Loop to send emails at fixed 20-second intervals (i.e. at 20, 40, and 60 seconds)
            for ($i = 1; $i <= $noOfMails; $i++) {
                Log::info("Waiting 20 seconds before sending email iteration {$i} for cart ID: {$cart->id}");
                sleep(20); // Wait 20 seconds

                // Check if the cart is still active
                if ($cart->is_cart_active == 0) {
                    Log::info("Cart ID: {$cart->id} is no longer active. Stopping email attempts.");
                    break;
                }

                // Attempt to send email
                if (!$this->sendEmail($cart)) {
                    Log::warning("Failed to send email for cart ID: {$cart->id}, iteration: {$i}");
                    continue;
                }

                Log::info("Email sent successfully for cart ID: {$cart->id}, iteration: {$i}");
                // Update email count and timestamp after each successful send
                $abandonedCart->increment('sent_email_count');
                $abandonedCart->update(['updated_at' => now()]);
            }
        }

        $this->info("Abandoned cart emails processed successfully.");
        Log::info("Finished processing abandoned cart emails.");
    }

    /**
     * Save or update abandoned cart data.
     */
    private function saveAbandonedCart($cart)
    {
        $storeId = Session::get('store_id');
        $customer = Customer::find($cart->customer_id);

        $abandonedCart = AbandonedCart::updateOrCreate(
            ['cart_id' => $cart->id],
            [
                'store_id'         => $storeId ?? null,
                'customer_id'      => $cart->customer_id,
                'email'            => $customer ? $customer->email : $cart->email,
                'is_active'        => $cart->is_cart_active,
                'sent_email_count' => 1,
            ]
        );
        // $cartId = 1; // use a valid cart ID
        // $abandonedCart = AbandonedCart::updateOrCreate(
        //     ['cart_id' => $cartId],
        //     [
        //         'store_id'         => 1,
        //         'customer_id'      => 1,
        //         'email'            => 'test@example.com',
        //         'is_active'        => 1,
        //         'sent_email_count' => 0
        //     ]
        // );
        // dd($abandonedCart);

        Log::info("Abandoned cart saved for cart ID: {$cart->id}, Store ID: {$storeId}");
    }

    /**
     * Queue email for abandoned cart.
     */
    private function sendEmail($cart)
    {
        $customer = Customer::find($cart->customer_id);
        if (!$customer || !$customer->email) {
            Log::error("No valid customer email for cart ID: {$cart->id}");
            return false;
        }

        $template = EmailTemplate::where('slug', 'abandoned_cart')->first();
        if (!$template) {
            Log::error("No abandoned cart email template found.");
            return false;
        }

        try {
            EmailQueue::create([
                'type'        => 'abandoned_cart',
                'template_id' => $template->id,
                'email'       => $customer->email,
                'content'     => json_encode(["email" => $customer->email], JSON_UNESCAPED_UNICODE),
            ]);

            Log::info("Email queued successfully for cart ID: {$cart->id}, email: {$customer->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to insert into EmailQueue: " . $e->getMessage());
            return false;
        }
    }

}
