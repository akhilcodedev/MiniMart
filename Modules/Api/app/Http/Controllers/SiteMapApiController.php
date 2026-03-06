<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Products\Jobs\SiteMapFeedExport;
use Illuminate\Http\JsonResponse;

class SiteMapApiController extends Controller
{
    /**
     * Queue the sitemap generation job.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate()
    {
        SiteMapFeedExport::dispatch();


        return response()->json([
            'status'  => true,
            'message' => 'Sitemap generation has been queued.',
        ]);
    }
    /**
     * Return the sitemap XML file if it exists.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getSiteMap()
    {

        $path = public_path('sitemap.xml');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Sitemap not found'], 404);
        }

        // Return the XML file with proper headers
        return response()->file($path, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
