<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Products\Jobs\GoogleProductFeedExport;

class FeedController extends Controller
{
    /**
     * Queue the Google product feed generation job.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateGoogleFeed(Request $request)
    {
        GoogleProductFeedExport::dispatch();

        return response()->json([
            'status'  => true,
            'message' => 'Google product feed generation has been queued.',
        ]);
    }

    /**
     * Return the Google product feed XML file if it exists.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getProductFeedXml()
    {
        $path = public_path('google-product-feed.xml');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Sitemap not found'], 404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
