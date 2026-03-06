<?php

namespace Modules\CMS\Http\Controllers;

use Modules\CMS\Models\CMS;
use Illuminate\Http\Request;
use Modules\CMS\Models\CMSMeta;
use Modules\CMS\Models\Language;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\URLRewrite\Models\UrlRewrite;   //add here
use Illuminate\Support\Str; // Import the Str class


class CMSController extends Controller
{
    /**
     * Get all CMS pages with their associated meta data.
     * @return /Returns a view with the cms pages data and languages
     */
    public function getCmsPages()
    {
        $pages = CMS::with('meta')->get();
        $languages = Language::pluck('name', 'id');
        return view('cms::pages.cms_pages', compact('pages', 'languages'));
    }

    /**
     * Show the form for creating a new CMS page.
     * @return / Returns a view for creating cms pages, with list of languages
     */
    public function create()
    {
        $languages = Language::pluck('name', 'id');
        return view('cms::pages.create', compact('languages'));
    }

    /**
     * Store a newly created CMS page in storage.
     * @param /The request object containing the CMS page data.
     * @return /Redirects to the index page with success message on success or back to form page with error message.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'language' => 'required',
            'content' => 'required|string',
            'publish' => 'required|boolean',
            'slug' => 'required|string|unique:cms_metas,slug',
            'meta-title' => 'required|string|max:255',
            'meta-key' => 'required|string|max:255',
            'meta-description' => 'nullable|string',
        ]);

        $validatedData['slug'] = Str::slug($validatedData['slug'], '-');

        try {
            DB::transaction(function () use ($validatedData) {
                $page = CMS::create([
                    'title' => $validatedData['title'],
                    'language' => $validatedData['language'],
                    'content' => $validatedData['content'],
                    'is_published' => $validatedData['publish'],
                ]);
                $page->meta()->create([
                    'slug' => $validatedData['slug'],
                    'meta_title' => $validatedData['meta-title'],
                    'meta_key' => $validatedData['meta-key'],
                    'meta_description' => $validatedData['meta-description'],
                ]);

                try {
                    UrlRewrite::create([
                        'entity_type' => 'cms',
                        'entity_id' => $page->id,
                        'request_path' =>  $validatedData['slug'],
                        'target_path' =>  'cms/' . $validatedData['slug'],
                    ]);
                } catch (\Exception $exception) {
                    Log::error('CMS URL Rewrite creation failed: ' . $exception->getMessage());
                    throw $exception;
                }
            });
            return redirect()->route('cms.pages')
                ->with('success', 'CMS Page created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create page: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified CMS page.
     * @param int $id The ID of the CMS page to edit.
     * @return / Returns a view for editing the cms page, with languages.
     */
    public function edit($id)
    {
        $page = CMS::with('meta')->findOrFail($id);
        $languages = Language::pluck('name', 'id');
        return view('cms::pages.edit', compact('page', 'languages'));
    }

    /**
     * Update the specified CMS page in storage.
     * @param / The request object containing the updated CMS page data.
     * @param int $id The ID of the CMS page to update.
     * @return / Redirects to the index page with success message on success or back to form page with error message.
     */
    public function update(Request $request, $id)
    {
        $cmsPage = CMS::with('meta')->findOrFail($id);
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'language' => 'required',
            'content' => 'required',
            'publish' => 'required|boolean',
            'slug' => 'required|max:255|unique:cms_metas,slug,' . $cmsPage->meta->id,
            'meta-title' => 'required|max:255',
            'meta-key' => 'required|max:255',
            'meta-description' => 'nullable|max:500',
        ]);

        $validatedData['slug'] = Str::slug($validatedData['slug'], '-');


        try {
            DB::transaction(function () use ($cmsPage, $validatedData) {
                $cmsPage->update([
                    'title' => $validatedData['title'],
                    'language' => $validatedData['language'],
                    'content' => $validatedData['content'],
                    'is_published' => $validatedData['publish'],
                ]);
                $cmsPage->meta()->update([
                    'slug' => $validatedData['slug'],
                    'meta_title' => $validatedData['meta-title'],
                    'meta_key' => $validatedData['meta-key'],
                    'meta_description' => $validatedData['meta-description'],
                ]);

                try {
                    $urlRewrite = UrlRewrite::where('entity_type', 'cms')
                        ->where('entity_id', $cmsPage->id)
                        ->first();
                    if ($urlRewrite) {
                        $urlRewrite->update([
                            'request_path' =>  $validatedData['slug'],
                            'target_path' =>  'cms/' . $validatedData['slug'],
                        ]);
                    } else {
                        UrlRewrite::create([
                            'entity_type' => 'cms',
                            'entity_id' => $cmsPage->id,
                            'request_path' =>  $validatedData['slug'],
                            'target_path' =>  'cms/' . $validatedData['slug'],
                        ]);
                    }
                } catch (\Exception $exception) {
                    Log::error('CMS URL Rewrite update failed: ' . $exception->getMessage());
                    throw $exception;
                }
            });
            return redirect()->route('cms.pages')->with('success', 'CMS Page updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update page: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified CMS page from storage.
     * @param int $id The ID of the CMS page to delete.
     * @return / Redirects to the index page with success message on success or back to the same page with error message.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $cmsPage = CMS::findOrFail($id);
                $cmsPage->meta()->delete();
                $cmsPage->delete();
            });
            return redirect()->route('cms.pages')
                ->with('success', 'CMS Page deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete page: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete the specified CMS page from storage.
     * @param /The request object containing ids of the CMS page to delete.
     * @return/ Returns a json response with the status message on success or back to the same page with error message.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No pages selected for deletion.',
                'icon' => 'error',
                'title' => 'Error!',
            ], 400);
        }
        try {
            DB::transaction(function () use ($ids) {
                CMSMeta::whereIn('cms_page_id', $ids)->delete();
                CMS::whereIn('id', $ids)->delete();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Selected pages deleted successfully.',
                'icon' => 'success',
                'title' => 'Success!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete pages: ' . $e->getMessage(),
                'icon' => 'error',
                'title' => 'Error!',
            ], 500);
        }
    }
}
