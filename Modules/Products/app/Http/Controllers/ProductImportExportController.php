<?php
namespace Modules\Products\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Products\Imports\ProductsImport;
use Modules\Products\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;

class ProductImportExportController extends Controller
{
    /**
     * Import products from an uploaded Excel or CSV file.
     *
     * @param Request $request The incoming HTTP request.
     * @return RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $limit = $request->input('limit', 100);

        Excel::import(new ProductsImport($limit), $request->file('file'));

        return back()->with('success', "Imported up to {$limit} products successfully!");
    }

    /**
     * Export products to an Excel file.
     *
     * @param Request $request The incoming HTTP request.
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $limit = $request->input('limit', 100);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new ProductsExport($limit, $startDate, $endDate), "products_export_{$limit}.xlsx");
    }
}
