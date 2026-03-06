<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Products\Jobs\SiteMapFeedExport;

class SitemapController extends Controller
{
    /**
     * Queue a sitemap generation process.
     *
     * Dispatches the `SiteMapFeedExport` job to generate the sitemap in the background.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(): JsonResponse
    {
        SiteMapFeedExport::dispatch();

        return response()->json([
            'status'  => true,
            'message' => 'Sitemap generation has been queued.',
        ]);
    }
}
