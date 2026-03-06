<?php

namespace Modules\CMS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CMS\Models\Banner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    /**
     * Display a listing of the hero banners.
     * @return /The index view with hero banners data.
     */
    public function index()
    {
        $hero_banners = Banner::latest()->get();
        return view('cms::banners.index', compact('hero_banners'));
    }

    /**
     * Show the form for creating a new hero banner.
     * @return / The create banner form view.
     */
    public function create()
    {
        return view('cms::banners.create');
    }

    /**
     * Store a newly created hero banner in storage.
     * @param  /The request object containing banner data.
     * @return /Redirects to the index page with success message on success or back to form page with error message.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'subtitle' => 'nullable|max:255',
            'description' => 'nullable',
            'banner_images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alt_tag' => 'nullable|max:255',
            'position' => 'nullable|integer',
            'status' => 'nullable|boolean', 

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = $request->input('status', false); 

        $image = $request->file('banner_images')->store('banners/images', 'public');

        Banner::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'images' => [$image],
            'alt_tag' => $request->alt_tag,
            'position' => $request->position,
            'status' => $status,
        ]);

        return redirect()->route('banners.index')
            ->with('success', 'Hero banner created successfully.');
    }

    /**
     * Show the form for editing the specified hero banner.
     * @param int $id The ID of the hero banner to edit.
     * @return / The edit view with the hero banner data.
     */
    public function edit($id)
    {
        $hero_banner = Banner::findOrFail($id);

        return view('cms::banners.edit', compact('hero_banner'));
    }

    /**
     * Update the specified hero banner in storage.
     * @param /The request object with updated banner data.
     * @param /The ID of the hero banner to update.
     * @return /Redirects to the index page with success message on success or back to form page with error message.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'position' => 'nullable|integer|min:0',
            'banner_images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $heroBanner = Banner::findOrFail($id);
        $status = $request->has('status') ? true : false;

        $image = $heroBanner->images[0] ?? null;

        if ($request->hasFile('banner_images')) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }
            $image = $request->file('banner_images')->store('banners', 'public');
        }

        $heroBanner->update([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'description' => $request->input('description'),
            'position' => $request->input('position'), 
            'status' => $status,
            'images' => $image ? [$image] : [],
        ]);

        return redirect()->route('banners.index')->with('success', __('Hero Banner updated successfully.'));
    }

    /**
     * Remove the specified hero banner from storage.
     * @param \Modules\CMS\Models\Banner $hero_banner The hero banner model instance to be deleted (route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirects to the index page with success message on success.
     */
    public function destroy(Banner $hero_banner)
    {
        Log::info('Hero Banner:', ['hero_banner' => $hero_banner]);

        if (!empty($hero_banner->images)) {
            foreach ($hero_banner->images as $image) {
                if ($image && Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                    Log::info('Deleted image from storage', ['image' => $image]);
                } else {
                    Log::warning('Image not found or path is null', ['image' => $image]);
                }
            }
        }

        $hero_banner->delete();

        return redirect()->route('banners.index')
            ->with('success', 'Hero banner deleted successfully.');
    }

    /**
     * Delete an image from the specified hero banner.
     * @param  \Illuminate\Http\Request $request The request object with the banner ID and image URL.
     * @return \Illuminate\Http\JsonResponse JSON response indicating the success or failure of the image deletion.
     */
    public function deleteBannerImage(Request $request)
    {
        Log::info('Delete request received', $request->all());

        $hero_banner = Banner::find($request->banner_id);

        if (!$hero_banner) {
            Log::error('Hero Banner not found for ID: ' . $request->banner_id);
            return response()->json([
                'success' => false,
                'message' => 'Hero banner not found.'
            ]);
        }

        $images = $hero_banner->images ?? [];
        $imageUrl = $request->url;

        $key = array_search($imageUrl, $images);
        if ($key === false) {
            Log::warning('Image URL not found in hero_banner images', ['url' => $imageUrl]);
            return response()->json([
                'success' => false,
                'message' => 'Image not found in the hero banner.'
            ]);
        }

        if (Storage::disk('public')->exists($imageUrl)) {
            Storage::disk('public')->delete($imageUrl);
            Log::info('Image deleted from storage', ['path' => $imageUrl]);
        } else {
            Log::warning('Image not found in storage', ['path' => $imageUrl]);
        }


        unset($images[$key]);
        $hero_banner->images = array_values($images);
        $hero_banner->save();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.'
        ]);
    }
}