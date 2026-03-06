<?php
namespace Modules\Products\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Products\Models\Product;

class GoogleProductFeedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Any constructor code if needed.
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch only parent products (is_variant != 1)
        $products = Product::where('is_variant', '!=', 1)->get();

        // Create a new DOMDocument instance for XML generation.
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Create the <rss> root element and set attributes.
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $dom->appendChild($rss);

        // Create the <channel> element and add basic channel information.
        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        // Customize these values as needed.
        $channel->appendChild($dom->createElement('title', 'Store-9'));
        $channel->appendChild($dom->createElement('link', config('app.frontend_staging_url')));
        $channel->appendChild($dom->createElement('description', 'Google Product Feed for Your Store'));

        // Loop through each product and create an <item> element.
        foreach ($products as $product) {
            // Skip products missing required fields (e.g. url_key)
            if (empty($product->url_key)) {
                \Log::warning('Product missing url_key', ['id' => $product->id]);
                continue;
            }

            $item = $dom->createElement('item');
            $channel->appendChild($item);

            // Add required fields for Google Merchant Feed.
            $item->appendChild($dom->createElement('g:id', $product->id));
            $item->appendChild($dom->createElement('title', htmlspecialchars($product->name)));
            $item->appendChild($dom->createElement('description', htmlspecialchars($product->description)));

            // Build product URL based on your frontend structure.
            $link = config('app.frontend_staging_url') . '/' . $product->url_key;
            $item->appendChild($dom->createElement('link', $link));

            // Assuming you have a field for the product image.
            $imageLink = $product->image_link ?? '';
            $item->appendChild($dom->createElement('g:image_link', $imageLink));

            // Format the price (ensure you append the proper currency, e.g. USD)
            $price = number_format($product->price, 2) . ' USD';
            $item->appendChild($dom->createElement('g:price', $price));

            // Set product condition; assume 'new' unless you have other logic.
            $item->appendChild($dom->createElement('g:condition', 'new'));

            // Determine availability, for example based on stock quantity.
            $availability = $product->stock > 0 ? 'in stock' : 'out of stock';
            $item->appendChild($dom->createElement('g:availability', $availability));
        }

        // Save the generated feed to a file in the public directory.
        $feedPath = public_path('google-product-feed.xml');
        $dom->save($feedPath);

        \Log::info('Google Product Feed generated successfully', ['path' => $feedPath]);
    }
}
