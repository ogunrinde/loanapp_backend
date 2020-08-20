<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repayments;
use Illuminate\Support\Facades\Validator;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;

class LoanrepaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    public function repayments_lender(Request $request)
    {
        $request = Repayments::with(['lender','request', 'borrower'])->where(['lender_id' => $request->user()->id])->get();
        return response()->json(['status' => 'success', 'repayments'=>$request]);
    }

    public function repayments_borrower(Request $request)
    {
        $request = Repayments::with(['lender','request', 'borrower'])->where(['borrower_id' => $request->user()->id])->get();
        return response()->json(['status' => 'success', 'repayments'=>$request]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'lender_id' => 'required|numeric',
            'borrower_request_id' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'date_paid' => 'required|date|date_format:Y-m-d',
            'mode_of_payment' => 'required|string'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $lender = User::where(['lender_id' => $request->lender_id])->first();

        if($lender == null)
        {
            $error['message'] = "Lender not Found";
            return response(['status' => 'success', 'error' => $error]);
        }    

        $data['borrower_id'] = $request->user()->id;

        $res = Repayments::Create($data);

        if(env('APP_ENV') != 'local')
            $this->mail("Loan Repayment", $lender->name, $lender->email, $data);

        return response(['status' => 'success', 'repayment' => $res]);
    }


    public function mail($subject,$name, $email, $data)
    {
       $data = array("subject" => $subject, "name" => $name , "email" => base64_encode($email), 'repayment' => $data);
       Mail::to($email)->send(new Activitymail($data));
       
       return true;
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
