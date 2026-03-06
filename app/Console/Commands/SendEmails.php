<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use App\Jobs\SendEmailJob;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will trigger email sending for queued emails every minute.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Starting the email sending process...");

        $queuedEmails = EmailQueue::where('is_dispatched', 0)->get();

        if ($queuedEmails->isEmpty()) {
            Log::info("No emails found in the queue.");
            return;
        }

        foreach ($queuedEmails as $emailQueue) {
            SendEmailJob::dispatch($emailQueue->subject, $emailQueue->email, $emailQueue->content, $emailQueue->template_id);
            $emailQueue->is_dispatched = 1;
            $emailQueue->save();
        }

        Log::info("Email sending process completed.");
    }
}
