<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCustomEmail;
use Modules\WebConfigurationManagement\Models\EmailTemplate;


class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $subject;
    public $to;
    public $content;
    public $template_id;

    /**
     * Create a new job instance.
     */
    public function __construct($subject, $to, $content, $template_id)
    {
        $this->subject = $subject;
        $this->to = $to;
        $this->content = $content;
        $this->template_id = $template_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $template = EmailTemplate::find($this->template_id);
        if ($template) {
            $parsedContent = $this->replaceVariables($template->content, json_decode($this->content, true));
            Mail::to($this->to)->send(new SendCustomEmail($this->subject, $parsedContent));
        } else {
            \Log::error("Email template with ID {$this->template_id} not found.");
        }
    }



    /**
     * Replace placeholders with actual content values.
     */
    private function replaceVariables(string $templateContent, array $variables): string
    {
        foreach ($variables as $key => $value) {
        $templateContent = str_replace('{' . $key . '}', $value, $templateContent);
        }
        $templateContent = preg_replace('/\{.*?\}/', '', $templateContent);

        return $templateContent;
    }

}
