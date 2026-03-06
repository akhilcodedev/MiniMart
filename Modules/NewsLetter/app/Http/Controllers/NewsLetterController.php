<?php

namespace Modules\NewsLetter\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Queue;
use Modules\NewsLetter\Models\Newsletter;
use Modules\NewsLetter\Jobs\SendSubscriptionEmail;
use Modules\WebConfigurationManagement\Models\EmailQueue;
use Modules\WebConfigurationManagement\Models\EmailTemplate;


class NewsLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $newsletters = Newsletter::all();
        return view('newsletter::newsletters.index', compact('newsletters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('newsletter::newsletters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletters',
            'status' => 'required|in:subscribed,unsubscribed'
        ]);

        $newsletter = Newsletter::create([
            'email' => $request->email,
            'status' => $request->status,
            'subscribed_at' => now(),
        ]);
        $this->sendSubscriptionEmail($newsletter);
        event(new EmailQueue(['type' => 'news_letter', 'email' => $newsletter->email, 'content' => json_encode(["email" => $newsletter->email, "status" => $newsletter->status], JSON_UNESCAPED_UNICODE)]));

        $message = $newsletter->status === 'subscribed' ?  'Subscription Added and email will be sent!' : 'Subscription Added!';
        return redirect()->route('newsletters.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        return view('newsletter::newsletters.edit', compact('newsletter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletters,email,' . $id,
            'status' => 'required|in:subscribed,unsubscribed'
        ]);
        $newsletter = Newsletter::findOrFail($id);
        $originalStatus = $newsletter->status;
        $newsletter->update($request->all());

        if ($newsletter->status === 'subscribed' && $originalStatus !== 'subscribed') {
            $this->sendSubscriptionEmail($newsletter);
            return redirect()->route('newsletters.index')->with('success', 'Subscription Updated and email will be sent!');
        }
        return redirect()->route('newsletters.index')->with('success', 'Subscription Updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();
        return redirect()->route('newsletters.index')->with('success', 'Subscription Deleted!');
    }

    /**
     * Send a subscription email to the specified newsletter subscriber.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendEmail($id)
    {
        $newsletter = Newsletter::findOrFail($id);

        if ($newsletter->status !== 'subscribed') {
            return redirect()->route('newsletters.index')->with('error', 'Cannot send email to an unsubscribed address.');
        }

        $this->sendSubscriptionEmail($newsletter);

        return redirect()->route('newsletters.index')->with('success', 'Email has been sent successfully!');
    }

    /**
     * Queue a subscription email for the given newsletter subscriber.
     *
     * @param \App\Models\Newsletter $newsletter
     * @return void
     */
    private function sendSubscriptionEmail(Newsletter $newsletter)
    {
        $template = EmailTemplate::where('slug', 'news_letter')->first();
        if ($template) {
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
