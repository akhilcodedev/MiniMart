<?php

namespace Modules\Api\Http\Controllers;

use Modules\WebConfigurationManagement\Models\OssConfiguration;
use Modules\WebConfigurationManagement\Models\EmailTemplate;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class OssConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configurations = OssConfiguration::all();
        return response()->json($configurations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('api::create');
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
            $data['guest_email']    = null;
        } else {
            $data['customer_email'] = null;
        }

        $configuration = OssConfiguration::create($data);

        return response()->json($configuration, Response::HTTP_CREATED);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $configuration = OssConfiguration::findOrFail($id);
        return response()->json($configuration);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $configuration = OssConfiguration::findOrFail($id);

        if (Auth::check()) {
            if ($configuration->customer_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
        } else {
            $guest_email = $request->input('guest_email');
            if (!$guest_email || $configuration->customer_email !== $guest_email) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
        }

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
        } else {
            $data['customer_email'] = $data['guest_email'];
            unset($data['guest_email']);
        }

        $configuration->update($data);

        return response()->json($configuration);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $configuration = OssConfiguration::findOrFail($id);

        if (Auth::check()) {
            if ($configuration->customer_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $configuration->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Queue a newsletter email based on OSS configuration.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail($id)
    {
        $configuration = OssConfiguration::findOrFail($id);

        $email = $configuration->guest_email ?? $configuration->customer_email;
        if (!$email) {
            return response()->json(
                ['error' => 'No email found for this configuration.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $template = EmailTemplate::where('slug', 'news_letter')->first();
        if (!$template) {
            return response()->json(
                ['error' => 'Email template not found.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $emailContent = [
            "email"      => $email,
            "product_id" => $configuration->product_id,
        ];

        EmailQueue::create([
            'type'        => 'news_letter',
            'template_id' => $template->id,
            'email'       => $email,
            'content'     => json_encode($emailContent, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['message' => 'Email queued successfully!'], Response::HTTP_OK);
    }
}
