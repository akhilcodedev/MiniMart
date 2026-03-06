<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\WebConfigurationManagement\Models\EmailTemplate;

class SendCustomEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $htmlContent;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $htmlContent
     */
    public function __construct($subject, $htmlContent)
    {
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $headerContent = EmailTemplate::where('slug', 'header')->value('content') ??
            '<div style="background: #007bff; color: #ffffff; padding: 20px; text-align: center;">
                <h1>Your Company Name</h1>
                <p>Welcome to Our Community!</p>
             </div>';

        $footerContent = EmailTemplate::where('slug', 'footer')->value('content') ??
            '<div style="background: #f4f4f9; text-align: center; padding: 15px; color: #888888; font-size: 14px;">
                <p>© ' . date('Y') . ' Your Company Name. All rights reserved.</p>
                <p>
                    <a href="https://yourcompany.com" style="color: #007bff; text-decoration: none;">Visit our website</a> |
                    <a href="https://yourcompany.com/unsubscribe" style="color: #007bff; text-decoration: none;">Unsubscribe</a>
                </p>
             </div>';

        return $this->subject($this->subject)
                    ->view('emails.custom_email')
                    ->with([
                        'htmlContent' => $this->htmlContent,
                        'footerContent' => $footerContent,
                        'headerContent' => $headerContent,
                    ]);
    }
}
