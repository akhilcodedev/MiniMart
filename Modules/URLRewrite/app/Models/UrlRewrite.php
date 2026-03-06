<?php

namespace Modules\URLRewrite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Category\Models\Category;
use Modules\CMS\Models\CMSMeta;
use Modules\Products\Models\Product;
// use Modules\URLRewrite\Database\Factories\UrlRewriteFactory;

class UrlRewrite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
     protected $signature = 'import:data';

     protected $fillable = [
        'entity_type',
        'entity_id',
        'request_path',
        'target_path',
    ];

    public function handle()
    {
        $this->info('Importing data...');

        // Products
        $products = Product::all();
        $this->info('Products:');
        foreach ($products as $product) {
            $this->info("  Product ID: {$product->id}, Name: {$product->name}, URL Key: {$product->url_key}");
        }

        // CMS Pages
        $cmsPages = CMSMeta::all();
        $this->info('CMS Pages:');
        foreach ($cmsPages as $cmsPage) {
            $this->info("  CMS Page ID: {$cmsPage->id}, Slug: {$cmsPage->slug}");
            if ($cmsPage->urlRewrite) {
                $this->info("  URL Rewrite ID: {$cmsPage->urlRewrite->id}, URL Key: {$cmsPage->urlRewrite->request_path}");
            }
        }

        // Categories
        $categories = Category::all();
        $this->info('Categories:');
        foreach ($categories as $category) {
            $this->info("  Category ID: {$category->id}, Name: {$category->name}, URL Key: {$category->url_key}");
        }

        $this->info('Data import and display complete.');

        return 0;
    }
}
