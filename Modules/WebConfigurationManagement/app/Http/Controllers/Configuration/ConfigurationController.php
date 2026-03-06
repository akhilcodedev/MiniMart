<?php

namespace Modules\WebConfigurationManagement\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\AdditionalConfiguration;
use App\Models\ClientCountry;
use App\Models\ClientCurrency;
use App\Models\ClientLanguage;
use App\Models\Configuration;
use App\Models\MapProvider;
use App\Models\PaymentOption;
use App\Models\SmsProvider;
use App\Models\TimeZone;
use Illuminate\Container\Container;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Modules\WebConfigurationManagement\Models\CoreConfigData;
use Modules\WebConfigurationManagement\Models\Page;
use Modules\WebConfigurationManagement\Models\PageTranslation;


use function Illuminate\Validation\message;


class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
//        $permissionResult = $this->checkForPermission('show_configuration');
//        if(!is_null($permissionResult)){
//            return $permissionResult;
//        }
        $user = Auth::user();
        return view('webconfigurationmanagement::configurations.core-config')->with([
            'user' => $user,

        ]);
    }

    /**
     * Update configuration settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function configUpdate(Request $request)
    {
        $prefix = 'webconfigurations_';
        $fieldNames = array_keys($request->all());
        $configurations = [];

        $newCurrencyValue = null;
        foreach ($fieldNames as $field) {

            if ($field != '_token') {
                if($request->input($field) == 'on'){
                    $configurations[] = [
                        'config_path' => $prefix . $field,
                        'value' => 1
                    ];
                } else{
                    if ($field == 'primary_currency') {
                        $newCurrencyValue = (isset($request->$field) && is_numeric($request->$field) && ((int)trim($request->$field) > 0)) ? (int)trim($request->$field) : null;
                    }
                    $configurations[] = [
                        'config_path' => $prefix . $field,
                        'value' => $request->input($field)
                    ];
                }

            }
        }

        foreach ($configurations as $config) {
            CoreConfigData::updateOrCreate(
                ['config_path' => $config['config_path']],
                ['value' => $config['value']]
            );
        }

        if (!is_null($newCurrencyValue)) {
            $targetCurrency = Currency::find($newCurrencyValue);
            if ($targetCurrency) {
                $clientCurrencyObj = ClientCurrency::updateOrCreate([
                    'is_primary' => 1
                ], [
                    'currency_id' => $targetCurrency->id,
                    'doller_compare' => 1,
                    'client_code' => null,
                ]);
            }
        }

        return redirect()->route('configure.index')->with('success', 'Configurations updated successfully!');
    }

    /**
     * Update Web Configurations.
     */
    public function paymentMethodUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'exists:payment_options,id',
            'status' => 'nullable|boolean',
        ]);

        $paymentMethodIds = $validatedData['payment_methods'];
        $activeStatus = $validatedData['status'] ?? 0;
        PaymentOption::whereIn('id', $paymentMethodIds)
            ->update(['status' => 1]);
        PaymentOption::whereNotIn('id', $paymentMethodIds)
            ->update(['status' => 0]);

        return redirect()->back()->with('success', __('Payment options updated successfully.'));
    }

    /**
     * Display the configuration form for selecting a timezone.
     *
     * @return \Illuminate\View\View
     */
    public function showConfigForm()
    {
        $savedTimezone = CoreConfigData::where('config_path', 'webconfigurations_timezone')->value('value');
        $timezones = timezone_identifiers_list();
        return view('configurations.core-config', compact('timezones', 'savedTimezone'));
    }

}

