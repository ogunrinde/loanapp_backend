<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Overdues;

class OverdueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function overdueforborrower(Request $request)
    {
        $overdues = Overdues::with(['lender','request', 'borrower','paymentschedule'])->where(['borrower_id' => $request->user()->id,'status' => 'pending'])->get();
        return response()->json(['status' => 'success', 'borrower_overdues'=>$overdues]);
    }

    public function overdueforlender(Request $request)
    {
        $overdues = Overdues::with(['lender','request', 'borrower','paymentschedule'])->where(['lender_id' => $request->user()->id,'status' => 'pending'])->get();
        return response()->json(['status' => 'success', 'lender_overdues'=>$overdues]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
