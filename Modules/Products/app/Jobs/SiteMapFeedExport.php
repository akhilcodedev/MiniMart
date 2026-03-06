<?php
namespace Modules\Products\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Category\Models\Category;
use Modules\Products\Models\Product;
//use Modules\Cms\Models\CmsPage; // adjust namespace if needed
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SiteMapFeedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Define the languages your site supports
    protected $languages = ['en', 'ar'];

    public function handle()
    {
        // Create a new sitemap instance
        $sitemap = Sitemap::create();

        // Add the homepage for each language
        foreach ($this->languages as $lang) {
            $homepageUrl = config('app.frontend_staging_url') . '/' . $lang;
            $sitemap->add(
                Url::create($homepageUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0)
            );
        }

        // Add Products for each language
        Product::where('is_variant', '!=', 1)->chunk(500, function ($products) use ($sitemap) {
            foreach ($products as $product) {
                // Check that the URL key is available
                if (empty($product->url_key)) {
                    \Log::warning('Product missing url_key', ['id' => $product->id]);
                    continue;
                }
                foreach ($this->languages as $lang) {
                    $url = config('app.frontend_staging_url') . '/' . $lang . '/' . $product->url_key;
                    $sitemap->add(
                        Url::create($url)
                            ->setLastModificationDate($product->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                            ->setPriority(0.8)
                    );
                }
            }
        });

        // Add Categories for each language
        Category::chunk(500, function ($categories) use ($sitemap) {
            foreach ($categories as $category) {
                if (empty($category->url_key)) {
                    \Log::warning('Category missing slug', ['id' => $category->id]);
                    continue;
                }
                foreach ($this->languages as $lang) {
                    $url = config('app.frontend_staging_url') . '/' . $lang . '/' . $category->url_key;
                    $sitemap->add(
                        Url::create($url)
                            ->setLastModificationDate($category->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                }
            }
        });

        // Add CMS Pages for each language
//        CmsPage::chunk(500, function ($pages) use ($sitemap) {
//            foreach ($pages as $page) {
//                if (empty($page->slug)) {
//                    \Log::warning('CMS page missing slug', ['id' => $page->id]);
//                    continue;
//                }
//                foreach ($this->languages as $lang) {
//                    $url = config('app.url') . '/' . $lang . '/cms/page/' . $page->slug;
//                    $sitemap->add(
//                        Url::create($url)
//                            ->setLastModificationDate($page->updated_at)
//                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
//                            ->setPriority(0.6)
//                    );
//                }
//            }
//        });

        // Save the sitemap to a file in the public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
