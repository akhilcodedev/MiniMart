<?php

namespace Modules\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\WebConfigurationManagement\Models\EmailTemplate; // Update the model namespace if necessary.

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $templates = [
            [
                'slug' => 'header',
                'label' => 'Header Template',
                'subject' => 'Header Template',
                'tags' => '{header}',
                'content' => '<div style="background: #007bff; color: #ffffff; padding: 20px; text-align: center;"><h1>Your Company Name</h1><p>Welcome to Our Community!</p></div>',
            ],
            [
                'slug' => 'footer',
                'label' => 'Footer Template',
                'subject' => 'Footer Template',
                'tags' => '{footer}',
                'content' => '<div style="background: #f4f4f9; text-align: center; padding: 15px; color: #888888; font-size: 14px;"><p>© {{ date(\'Y\') }} Your Company Name. All rights reserved.</p><p><a href="https://yourcompany.com" style="color: #007bff; text-decoration: none;">Visit our website</a>|<a href="https://yourcompany.com/unsubscribe" style="color: #007bff; text-decoration: none;">Unsubscribe</a></p></div>',
            ],
            [
                'slug' => 'customer_register',
                'label' => 'Customer Registration',
                'subject' => 'Customer Registration',
                'tags' => '{first_name},{last_name},{customer_code},{email}',
                'content' => '<p>Dear Customer, {first_name} {last_name}</p><p>Thank you for registering with us! Your account has been successfully created.</p><p>Your Ref code is CODE: {customer_code}</p><p>We will send our new updates to your email: {email}</p><p>If you have any questions, feel free to contact us.</p><p>Best regards,<br>Store9</p>',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'label' => $template['label'],
                    'subject' => $template['subject'],
                    'tags' => $template['tags'],
                    'content' => trim($template['content']),
                ]
            );
        }
    }
}
