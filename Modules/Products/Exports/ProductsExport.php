<?php
namespace Modules\Products\Exports;
// ✅ Ensure namespace is correct
use Modules\Products\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $exportLimit;
    protected $startDate;
    protected $endDate;

    public function __construct($limit = 100, $startDate = null, $endDate = null)
    {
        $this->exportLimit = $limit;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Product::query();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->limit($this->exportLimit)->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'SKU', 'Name', 'Product Type ID', 'In Stock', 'URL Key',
            'Price', 'Special Price', 'Special Price From', 'Special Price To',
            'Quantity', 'Status', 'Related Products'
//            , 'Cross Selling Products', 'Created At'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->sku,
            $product->name,
            $product->product_type_id,
            $product->is_in_stock,
            $product->url_key,
            $product->price,
            $product->special_price,
            $product->special_price_from,
            $product->special_price_to,
            $product->quantity,
            $product->status,
            $product->related_products,
//            $product->cross_selling_products,
//            $product->created_at,
        ];
    }
}

