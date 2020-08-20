<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PaymentSchedules;

class PaymentSchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    public function borrower_payment_schedules(Request $request, $id)
    {
        $schedules = PaymentSchedules::with(['borrower','lender', 'request'])->where(['borrower_id' => $request->user()->id, 'borrower_request_id' => $id])->get();
        return response()->json(['status' => 'success', 'schedules'=>$schedules]);
    }

    public function lender_payment_schedules(Request $request, $id)
    {
        $schedules = PaymentSchedules::with(['borrower','lender', 'request'])->where(['lender_id' => $request->user()->id, 'borrower_request_id' => $id])->get();
        return response()->json(['status' => 'success', 'schedules'=>$schedules]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
