<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\CMS\Models\CmsStaticBlock;
use function Symfony\Component\Console\Style\success;

class CmsBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blocks = CmsStaticBlock::all();
        return view('cms::blocks.index', compact('blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cms::blocks.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Container\Container|mixed
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|unique:cms_static_blocks',
                'title' => 'required',
                'content_data' => 'required',
                'is_active' => 'boolean',
            ]);

            CmsStaticBlock::create([
                'identifier' => $request->identifier,
                'store_id' => session('store_id') ?? 1,
                'title' => $request->title,
                'content' => $request->content_data,
                'is_active' => $request->is_active,
            ]);

            return redirect()->route('cms-blocks.index')->with('success', 'CMS Block Created Successfully');
        } catch (\Exception $exception) {
            Log::error('Something went wrong when store cms block , Error ::' . $exception->getMessage() . " on Line :: " . $exception->getLine());
            return redirect()->back()->with('error', 'Something went wrong');
        }

    }

    /**
     * Show the form for editing the specified resource.
     * @param $id
     * @return \Illuminate\Container\Container|mixed
     */
    public function edit($id)
    {
        $cms_static_block = CmsStaticBlock::find($id);
        return view('cms::blocks.edit', compact('cms_static_block'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $cms_static_block = CmsStaticBlock::find($id);
        try {
            $request->validate([
                'title' => 'required',
                'content_data' => 'required',
                'is_active' => 'boolean',
            ]);

            $cms_static_block->update([
                'store_id' => session('store_id') ?? 1,
                'title' => $request->title,
                'content' => $request->content_data,
                'is_active' => $request->is_active,
            ]);
            return redirect()->route('cms-blocks.index')->with('success', 'CMS Block Updated Successfully');
        } catch (\Exception $exception) {
            Log::error('Something went wrong when update cms block , Error ::' . $exception->getMessage() . " on Line :: " . $exception->getLine());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return mixed
     */
    public function destroy(Request $request)
    {
        $cms_static_block =  CmsStaticBlock::find($request->id);
        try {
            $cms_static_block->delete();
            return response()->json([
                'status' => true,
                'message' => 'Block deleted successfully'
            ], 200);
        } catch (\Exception $exception) {
            Log::error('Something went wrong when delete cms block , Error ::' . $exception->getMessage() . " on Line :: " . $exception->getLine());
            return response()->json([
                'status' => false,
                'message' => 'something went wrong',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
