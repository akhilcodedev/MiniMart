<?php

namespace Modules\PriceRuleManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\PriceRuleManagement\Models\ProductCatalogPrice;
use Modules\PriceRuleManagement\Traits\DiscountApply;
use Modules\Products\Models\Product;

class ApplyCatalogPriceRuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DiscountApply;

    public $timeout = 3600;
    public $retryAfter = 4000;
    public $tries = 2;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $products = Product::all();
        foreach ($products as $product) {
            $updatedPrice = $this->applyPriceRuleToProduct($product);
            ProductCatalogPrice::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'original_price' => $product->price,
                    'updated_price' => $updatedPrice
                ]
            );
        }
    }
}
