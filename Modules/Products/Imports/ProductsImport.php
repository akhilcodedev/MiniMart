<?php
namespace Modules\Products\Imports; // ✅ Ensure namespace is correct

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Modules\Products\Models\Product;


class ProductsImport implements ToModel, WithHeadingRow, WithLimit
{
    protected $importLimit;

    public function __construct($limit = 100) // Default limit is 100
    {
        $this->importLimit = $limit;
    }

    public function model(array $row)
    {
        if (empty($row['sku'])) {
            \Log::error("Product import error: SKU is missing for row: " . json_encode($row));
            return null; // Skip rows with missing SKU
        }
        return Product::updateOrCreate(
            ['sku' => $row['sku']], // Check for existing SKU
            [
                'name' => $row['name'] ?? null,
                'product_type_id' => $row['product_type_id'] ?? null,
                'is_in_stock' => $row['is_in_stock'] ?? null,
                'url_key' => $row['url_key'] ?? null,
                'price' => $row['price'] ?? null,
                'special_price' => $row['special_price'] ?? null,
                'special_price_from' => $row['special_price_from'] ?? null,
                'special_price_to' => $row['special_price_to'] ?? null,
                'quantity' => $row['quantity'] ?? null,
                'status' => $row['status'] ?? null,
                'related_products' => $row['related_products'] ?? null,
                'cross_selling_products' => $row['cross_selling_products'] ?? null,

        ]);
    }

    public function limit(): int
    {
        return $this->importLimit;
    }
}
