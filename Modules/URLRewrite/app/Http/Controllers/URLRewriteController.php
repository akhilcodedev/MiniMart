<?php

namespace Modules\URLRewrite\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\URLRewrite\Models\UrlRewrite;
use Modules\Products\Models\Product;
use Modules\CMS\Models\CMSMeta;
use Modules\Category\Models\Category;

class URLRewriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $urlRewrites = UrlRewrite::all();
        $products = Product::all();
        $cmsPages = CMSMeta::all();
        $categories = Category::all();

        return view('urlrewrite::index', compact('urlRewrites', 'products', 'cmsPages', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('urlrewrite::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'old_url' => 'required|string|max:255|unique:url_rewrites,old_url',
            'new_url' => 'required|string|max:255',
        ]);

        $urlRewrite = new UrlRewrite();
        $urlRewrite->old_url = $request->input('old_url');
        $urlRewrite->new_url = $request->input('new_url');
        $urlRewrite->save();

        return redirect()->route('urlrewrite.index')->with('success', 'URL Rewrite created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $urlRewrite = UrlRewrite::findOrFail($id);
        return view('urlrewrite::edit', compact('urlRewrite'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    $request->validate([
        'request_path' => 'required|string|max:255|unique:url_rewrites,request_path,'.$id, // Include $id to ignore the current record in uniqueness check
        'target_path' => 'required|string|max:255',
    ]);

    $urlRewrite = UrlRewrite::findOrFail($id);
    $urlRewrite->request_path = $request->input('request_path');
    $urlRewrite->target_path = $request->input('target_path');
    $urlRewrite->save();

    return redirect()->route('urlrewrite.index')->with('success', 'URL Rewrite updated successfully!');
    }

}
