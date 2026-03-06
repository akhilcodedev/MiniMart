<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CMS\Models\Banner;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllBanners()
    {
        try {
            $banners = Banner::all();

            if ($banners->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No banners available.',
                ], 404);
            }

            $formattedBanners = $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'description' => $banner->description,
                    'images' => $banner->images,
                    'alt_tag' => $banner->alt_tag,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Banners fetched successfully.',
                'data' => $formattedBanners,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show category banner image
     *
     * @param [type] $filename
     * @return void
     */
    public function showImage($filename)
    {
        try {
            $imagePath = public_path('storage/banners/images/' . $filename);

            if (!file_exists($imagePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found.',
                ], 404);
            }

            return response()->file($imagePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


  
}
