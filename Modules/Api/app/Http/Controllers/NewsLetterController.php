<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Modules\NewsLetter\Models\Newsletter;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;

class NewsLetterController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $newsletters = Newsletter::all();
        return response()->json(['newsletters' => $newsletters], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters',
            'status' => 'required|in:subscribed,unsubscribed'
        ]);

        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletter = Newsletter::create([
            'email' => $request->email,
            'status' => $request->status,
            'subscribed_at' => now(),
        ]);
        $this->sendSubscriptionEmail($newsletter);
        event(new EmailQueue($newsletter->toArray()));
        $message = $newsletter->status === 'subscribed' ?  'Subscription Added and email will be sent!' : 'Subscription Added!';
        return response()->json(['message' => $message, 'newsletter' => $newsletter], 201); // 201 Created
    }

  /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email,' . $id,
            'status' => 'required|in:subscribed,unsubscribed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletter = Newsletter::findOrFail($id);
         $originalStatus = $newsletter->status;
        $newsletter->update($request->all());

        if ($newsletter->status === 'subscribed' && $originalStatus !== 'subscribed') {
           $this->sendSubscriptionEmail($newsletter);
             event(new EmailQueue($newsletter->toArray()));
           return response()->json(['message' => 'Subscription Updated and email will be sent!', 'newsletter' => $newsletter], 200);
        }

        return response()->json(['message' => 'Subscription Updated!', 'newsletter' => $newsletter], 200);
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        return response()->json(['message' => 'Subscription Deleted!'], 200);
    }

    /**
     * Send a subscription email to a subscribed newsletter user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
     public function sendEmail($id)
    {
       $newsletter = Newsletter::findOrFail($id);

        if ($newsletter->status !== 'subscribed') {
            return response()->json(['error' => 'Cannot send email to an unsubscribed address.'], 400);
        }

        $this->sendSubscriptionEmail($newsletter);
        return response()->json(['success' => 'Email has been sent successfully!'], 200);
    }

    /**
     * Queue a newsletter subscription email.
     *
     * @param \App\Models\Newsletter $newsletter
     * @return void
     */
    private function sendSubscriptionEmail(Newsletter $newsletter)
    {
         $template = EmailTemplate::where('slug', 'news_letter')->first();
            if($template) {
                $emailContent = [
                    "email" => $newsletter->email,
                    "status" => $newsletter->status,
                ];

                   EmailQueue::create([
                    'type' => 'news_letter',
                    'template_id' => $template->id,
                    'email' => $newsletter->email,
                    'content' => json_encode($emailContent, JSON_UNESCAPED_UNICODE),
                ]);
            }
     }

}
