<?php

namespace Modules\WebConfigurationManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\WebConfigurationManagement\Imports\ClientDataImport;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class ImportController extends Controller
{
    /**
     * Import data from an uploaded file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'type' => 'required|in:currency,language,country,timezone,map_providers',
        ]);
        try {
            Excel::import(new ClientDataImport($request->type), $request->file('file'));
            return back()->with('success', 'File imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'There was an error importing the file: ' . $e->getMessage());
        }
    }

    /**
     * Download a sample file from the assets directory.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadSampleFile($filename)
    {
        $file = public_path('assets/sample-files/' . $filename);
        if (file_exists($file)) {
            return response()->download($file);
        } else {
            return redirect()->back()->with('error', 'File not found.');
        }
    }


}
