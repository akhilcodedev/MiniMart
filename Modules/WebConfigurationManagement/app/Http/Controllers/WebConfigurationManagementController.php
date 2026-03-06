<?php

namespace Modules\WebConfigurationManagement\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\WebConfigurationManagement\Models\Country;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;
use Modules\WebConfigurationManagement\Models\TimeZone;
use Modules\WebConfigurationManagement\Models\CoreConfigData;


class WebConfigurationManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('webconfigurationmanagement::index');
    }

    /**
     * Display the appropriate configuration form based on menu, submenu, and form type.
     *
     * @param string $menu The main menu category.
     * @param string $submenu The submenu under the main menu.
     * @param string $form The specific form to be displayed.
     * @return \Illuminate\View\View
     */
    public function showForm($menu, $submenu, $form)
    {
        if ($menu === 'system-configuration') {
            $timezones = TimeZone::all(['id', 'timezone']);

            if ($submenu === 'timezone' && $form === 'timezone-form') {
                return view('webconfigurationmanagement::system-configuration.system', compact('timezones'));
            }

            if ($submenu === 'datetime' && $form === 'datetime-form') {
                return view('webconfigurationmanagement::system-configuration.system', compact('timezones'));
            }

            if ($submenu === 'languages' && $form === 'languages-form') {
                return view('webconfigurationmanagement::system-configuration.system', compact('timezones'));
            }

            if ($submenu === 'smtp' && $form === 'smtp-form') {
                return view('webconfigurationmanagement::system-configuration.email-smtp');
            }

            if ($submenu === 'oss' && $form === 'oss-form') {
                return view('webconfigurationmanagement::system-configuration.oss');
            }

            if ($submenu === 'otp' && $form === 'otp-form') {
                return view('webconfigurationmanagement::system-configuration.otp');
            }


            if ($submenu === 'tax-configuration' && $form === 'tax-support-form') {
                $countries = Country::all();

                $latestTaxValue = DB::table('core_config_data')
                    ->where('config_path', 'web_configuration_tax_value')
                    ->latest('updated_at')
                    ->first();

                $latestTaxType = DB::table('core_config_data')
                    ->where('config_path', 'web_configuration_tax_type')
                    ->latest('updated_at')
                    ->first();

                return view('webconfigurationmanagement::system-configuration.tax', compact('countries', 'latestTaxValue', 'latestTaxType'));
            }


            if ($submenu === 'cart' && $form === 'cart-form') {
                $sendNotification = CoreConfigData::where('config_path', 'cart.send_notification')->value('value');
                $abandonedCartDays = CoreConfigData::where('config_path', 'cart.abandoned_cart_days')->value('value') ?? 7; // Default to 7 days
                $noOfMails = CoreConfigData::where('config_path', 'cart.no_of_mails')->value('value') ?? 1; // Default to 1 email
                $emailGapDays = CoreConfigData::where('config_path', 'cart.email_gap_days')->value('value') ?? 1; // Default to 1 day

                return view('webconfigurationmanagement::system-configuration.cart', [
                    'abandonedCartDays' => $abandonedCartDays,
                    'noOfMails' => $noOfMails,
                    'sendNotification' => $sendNotification ?? '0',
                    'emailGapDays' => $emailGapDays,
                ]);
            }

            if ($submenu === 'mail' && $form === 'test-mail') {
                $testEmail = CoreConfigData::where('config_path', 'mail.test_email')->value('value') ?? '';

                return view('webconfigurationmanagement::system-configuration.test-mail', [
                    'testEmail' => $testEmail,
                ]);
            }

        }

        if ($menu === 'customer-configuration') {
            if ($submenu === 'support' && $form === 'customer-support-form') {
                return view('webconfigurationmanagement::system-configuration.customer');
            }

        }
        $timezones = TimeZone::all(['id', 'timezone']);
        return view('webconfigurationmanagement::system-configuration.system', compact('timezones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        $latestTaxValue = CoreConfigData::where('config_path', 'web_configuration_tax_value')
            ->latest('updated_at')
            ->first();
        $latestTaxType = null;
        if ($latestTaxValue) {
            $latestTaxType = CoreConfigData::where([
                'country_id'  => $latestTaxValue->country_id,
                'config_path' => 'web_configuration_tax_type'
            ])->first();
        }
        return view('webconfigurationmanagement::system-configuration.tax', [
            'countries'      => $countries,
            'latestTaxValue' => $latestTaxValue,
            'latestTaxType'  => $latestTaxType
        ]);
    }

    /**
     * Saves the configuration settings for abandoned cart functionality.
     *
     * This method validates the input parameters (abandoned cart days, number of emails,
     * send notification flag, and email gap days), then updates or creates the corresponding
     * entries in the `CoreConfigData` table to store these settings.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request object containing the abandoned cart configuration data.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with a success message upon completion.
     */

    public function saveAbandonedCartConfig(Request $request)
    {
        $validatedData = $request->validate([
            'abandoned_cart_days' => 'required|integer|min:1|max:30',
            'no_of_mails' => 'required|integer|min:1|max:5',
            'send_notification' => 'required|boolean',
            'email_gap_days' => 'required|integer|min:1|max:7', // Added validation for email gap days
        ]);

        $configurations = [
            'cart.abandoned_cart_days' => $validatedData['abandoned_cart_days'],
            'cart.no_of_mails' => $validatedData['no_of_mails'],
            'cart.send_notification' => $validatedData['send_notification'],
            'cart.email_gap_days' => $validatedData['email_gap_days'], // Added email gap days
        ];

        foreach ($configurations as $key => $value) {
            CoreConfigData::updateOrCreate(
                ['config_path' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Abandoned Cart Settings Updated Successfully');
    }

    /**
     * Sends a test email to the specified address.
     *
     * This method validates the provided email address, stores it in the `CoreConfigData` table,
     * retrieves the 'test_mail' email template, creates a new entry in the `EmailQueue` to send the test email,
     * and logs the success or failure of the operation.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request object containing the test email address.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with a success or error message.
     */
    public function sendTestMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = $request->input('test_email');

        CoreConfigData::updateOrCreate(
            ['config_path' => 'mail.test_email'],
            ['value' => $testEmail]
        );

        $template = EmailTemplate::where('slug', 'test_mail')->first();
        if (!$template) {
            Log::error("No test email template found.");
            return redirect()->back()->with('error', 'Test email template not found.');
        }

        try {
            EmailQueue::create([
                'type'        => 'test_mail',
                'template_id' => $template->id,
                'email'       => $testEmail,
                'content'     => json_encode(["email" => $testEmail], JSON_UNESCAPED_UNICODE),
            ]);

            Log::info("Test email queued successfully for email: {$testEmail}");
            return redirect()->back()->with('success', 'Test email added to the queue successfully!');
        } catch (\Exception $e) {
            Log::error("Failed to insert into EmailQueue: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue test email.');
        }
    }

    /**
     * Stores the tax configuration.
     *
     * This method validates the request data, retrieves existing tax value and type configurations,
     * updates them if they exist, or creates new configurations if they don't.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse Redirects to the 'tax-config.create' route with a success message.
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'value'      => 'required|string',
            'tax_type'   => 'required|in:inclusive,exclusive'
        ]);

        $taxValue = CoreConfigData::where('config_path', 'web_configuration_tax_value')->first();

        if ($taxValue) {
            $taxValue->update([
                'country_id' => $validatedData['country_id'],
                'value'      => $validatedData['value']
            ]);
        } else {
            $taxValue = CoreConfigData::create([
                'country_id'  => $validatedData['country_id'],
                'config_path' => 'web_configuration_tax_value',
                'value'       => $validatedData['value']
            ]);
        }

        $taxType = CoreConfigData::where('config_path', 'web_configuration_tax_type')->first();

        if ($taxType) {            $taxType->update([
            'country_id' => $validatedData['country_id'],
            'value'      => $validatedData['tax_type']
        ]);
        } else {
            $taxType = CoreConfigData::create([
                'country_id'  => $validatedData['country_id'],
                'config_path' => 'web_configuration_tax_type',
                'value'       => $validatedData['tax_type']
            ]);
        }

        return redirect()->route('tax-config.create')->with('success', 'Tax configuration updated successfully');
    }


}
