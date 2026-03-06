<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\WebConfigurationManagement\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of all email templates.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $emailTemplates = EmailTemplate::all();
        return view('cms::email-template.index')->with(["emailTemplates" => $emailTemplates ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
       {

        $request->merge([
            'slug' => strtolower(trim($request->input('slug'))),
        ]);

        $validatedData = $request->validate([
           'subject' => 'required|string|max:255',
           'tags' => 'required|string',
           'content' => 'required|string',
           'label' => 'required|string',
           'slug' => 'required|string|unique:email_templates,slug',
        ]);

       try {
           $emailTemplate = EmailTemplate::create([
               'subject' => $validatedData['subject'] ?? null,
               'tags' => $validatedData['tags'] ?? null,
               'content' => $validatedData['content'] ?? null,
               'label' => $validatedData['label'] ?? null,
               'slug' => strtolower(trim($validatedData['slug'])) ?? null,
           ]);

           return redirect()->back()->with('success', 'Email template created successfully');
       } catch (\Exception $e) {
           return redirect()->back()
               ->with('error', 'Failed to create email template: ' . $e->getMessage())
               ->withInput();
       }
   }

     /**
         * Show the form for editing the specified resource.
         */
    public function edit($id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);
        return view('cms::email-template.edit', compact('emailTemplate'));
    }

     /**
         * Update the specified resource in storage.
         */
    public function update(Request $request, $id)
    {
        $request->merge([
            'slug' => strtolower(trim($request->input('slug'))),
        ]);

        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'tags' => 'required|string',
            'content' => 'required|string',
            'label' => 'required|string',
            'slug' => 'required|string|unique:email_templates,slug,' . $id,
        ]);

        try {
            $emailTemplate = EmailTemplate::findOrFail($id);
            $emailTemplate->update([
                'subject' => $validatedData['subject'] ?? null,
                'tags' => $validatedData['tags'] ?? null,
                'content' => $validatedData['content'] ?? null,
                'label' => $validatedData['label'] ?? null,
                'slug' => strtolower(trim($validatedData['slug'])) ?? null,
            ]);

            return redirect()->back()->with('success', 'Email template updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update email template: ' . $e->getMessage())
                ->withInput();
            }
        }

    /**
         * Remove the specified resource from storage.
         */
    public function destroy($id)
    {
        try {
            $emailTemplate = EmailTemplate::findOrFail($id);
            $emailTemplate->delete();

            return redirect()->back()->with('success', 'Email template deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete email template: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple email templates by their IDs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        try {
            EmailTemplate::whereIn('id', $ids)->delete();
            return response()->json([
                'title' => 'Success!',
                'message' => 'Selected templates deleted successfully.',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'title' => 'Error!',
                'message' => 'Failed to delete templates: ' . $e->getMessage(),
                'icon' => 'error'
            ], 500);
        }
    }

    /**
     * Display the email header and footer template view.
     *
     * @return \Illuminate\View\View
     */
    public function emailHeaderFooter(){
       return view('cms::email-template.header-footer-index');
    }

    /**
     * Store or update the email header and footer content.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emailHeaderFooterStore(Request $request){
       try{

       }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete email template: ' . $e->getMessage());
       }
    }

    /**
     * Show the form to edit the header and footer of an email template.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editEmailHeaderFooter($id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);
        return view('cms::email-template.edit', compact('emailTemplate'));
    }

    /**
     * Update the email header and footer content.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emailHeaderFooterUpdate(Request $request){
       try{

       }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete email template: ' . $e->getMessage());
       }
    }
}
