<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = config('app.name');
        $pageSubTitle = 'Dashboard';

        return view('base::index', compact(
            'pageTitle',
            'pageSubTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('base::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('base::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('base::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Perform a user health check.
     *
     * This method updates the authenticated user's last activity timestamp and checks if the user is logged in.
     *
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function getUserHealthCheck(){
        try{
            $msg =  "health check success";
            $user = Auth::user();
            if ($user) {
                $user->updated_at = Carbon::now()->getTimestamp();
                $user->timestamps = false;
                $user->save();
            } else {
                $msg =  "health check error : User logged out!";
            }

        }catch (\Exception $e){
            $msg =  "health check error : " . $e->getMessage();
        }

        return response()->json([
            'message' => $msg
        ], 200);
    }
}
