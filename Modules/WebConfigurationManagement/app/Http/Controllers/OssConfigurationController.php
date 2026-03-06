<?php

namespace Modules\WebConfigurationManagement\Http\Controllers;

use Modules\WebConfigurationManagement\Models\OssConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;

class OssConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configurations = OssConfiguration::all();
        return view('webconfigurationmanagement::system-configuration.oss');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('webconfigurationmanagement::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|numeric',
        ];
        if (!Auth::check()) {
            $rules['guest_email'] = 'required|email';
        }
        $data = $request->validate($rules);

        if (Auth::check()) {
            $data['customer_id']    = Auth::id();
            $data['customer_email'] = Auth::user()->email;
        }

        $configuration = OssConfiguration::create($data);


        if (!Auth::check() && isset($data['guest_email'])) {
        }

        $this->sendSubscriptionEmail($configuration);

        return redirect()->route('oss-configurations.index')
            ->with('success', 'Configuration saved successfully.');
    }

    /**
     * Send email for a given configuration record.
     */
    public function sendEmail($id)
    {
        $configuration = OssConfiguration::findOrFail($id);
        $email = $configuration->guest_email ?? $configuration->customer_email;

        if (!$email) {
            return redirect()->route('oss-configurations.index')
                ->with('error', 'No email found for this configuration.');
        }

        $this->sendSubscriptionEmail($configuration);

        return redirect()->route('oss-configurations.index')
            ->with('success', 'Email has been queued successfully!');
    }

    /**
     * Helper function to send an email using the OssConfigurationMail mailable.
     */
    private function sendSubscriptionEmail(OssConfiguration $configuration)
    {
        $template = EmailTemplate::where('slug', 'out_of_stock')->first();

        if (!$template) {
            return;
        }

        $email = $configuration->guest_email ?? $configuration->customer_email;

        $emailContent = [
            "email"      => $email,
            "product_id" => $configuration->product_id,
        ];

        EmailQueue::create([
            'type'        => 'out_of_stock',
            'template_id' => $template->id,
            'email'       => $email,
            'content'     => json_encode($emailContent, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $configuration = OssConfiguration::findOrFail($id);
        return view('webconfigurationmanagement::show', compact('configuration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $configuration = OssConfiguration::findOrFail($id);
        return view('webconfigurationmanagement::edit', compact('configuration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $configuration = OssConfiguration::findOrFail($id);

        $rules = [
            'product_id' => 'required|numeric',
        ];
        if (!Auth::check()) {
            $rules['guest_email'] = 'required|email';
        }
        $data = $request->validate($rules);

        if (Auth::check()) {
            $data['customer_id']    = Auth::id();
            $data['customer_email'] = Auth::user()->email;
        }

        $configuration->update($data);

        return redirect()->route('oss-configurations.index')
            ->with('success', 'Configuration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $configuration = OssConfiguration::findOrFail($id);
        $configuration->delete();

        return redirect()->route('oss-configurations.index')
            ->with('success', 'Configuration deleted successfully.');
    }
}
