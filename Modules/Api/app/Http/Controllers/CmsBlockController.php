<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CMS\Models\CmsStaticBlock;

class CmsBlockController extends Controller
{
    /**
     *
     * get CMS static block by ID
     * @param $id
     * @return mixed
     */
    public function getBlockById($id){
        try{
            $block = CmsStaticBlock::where('id',$id)->where('is_active', true)->first();
            if($block){
                return response()->json([
                    'status' => true,
                    'message' => 'Block found successfully',
                    'data' => $block
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Block not found',
                ], 500);
            }
        }catch (\Exception $exception){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * get CMS static block by key
     * @param $key
     * @return mixed
     */
    public function getBlockByIdentifier($key){
        try{
            $block = CmsStaticBlock::where('identifier',$key)->where('is_active', true)->first();
            if($block){
                return response()->json([
                    'status' => true,
                    'message' => 'Block found successfully',
                    'data' => $block
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Block not found',
                ], 500);
            }
        }catch (\Exception $exception){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

}
