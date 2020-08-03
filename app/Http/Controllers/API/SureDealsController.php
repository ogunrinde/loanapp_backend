<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\suredeals;
use DB;
use App\ConnectBorrowerToLender;
use App\VaultWithdrawal;
use App\Surevault;

class SureDealsController extends Controller
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
            'Is_cash_disbursed' => 'required',
            'date_disbursed' => 'required|date',
            'mode_of_disbursement' => 'required',
            'Amount_disbursed' => 'required|numeric',
            'connectionId' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        DB::beginTransaction();

        try{
            //Create Deal
            $deal = suredeals::where(['lender_borrower_connection_id' => $request->connectionId])->first();

            if($deal == null)
            {
                $error['message'] = "Deal not Found";
                return response()->json(['status' => 'failed', 'error'=>$error]);  
            }
            $deal->Is_cash_disbursed = $request->Is_cash_disbursed;
            $deal->date_disbursed = $request->date_disbursed;
            $deal->mode_of_disbursement = $request->mode_of_disbursement;
            $deal->Amount_disbursed = $request->Amount_disbursed;
            $deal->save();

            //Create Withdrawal Table
            $connection = ConnectBorrowerToLender::find($request->connectionId);
            if($connection != null)
            {
                $data = array(
                "Amount_withdrawn" => $request->Amount_disbursed,
                "user_id" => $request->user()->id,
                "make_request_id" => $connection->borrower_request_id,
                "sure_vault_id"=> $connection->sure_vault_id
                );
                $res = VaultWithdrawal::Create($data);
            }  

            //Update Sure vault Table
            $vault = Surevault::find($connection->sure_vault_id);
            if($vault != null)
            {
                $vault->fundamount = (float)$vault->fundamount - (float)$request->Amount_disbursed;
                $vault->save();
            } 
            DB::commit();
            $request = suredeals::with(['borrower','lender','connect', 'request'])->where(['lender_id' => $request->user()->id,'Is_cash_disbursed' => '0'])->get();
            return response(['status' => 'success', 'suredeal' => $deal,'request'=>$request]);
        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);
        }  
    }

    public function vaultwithdrawal($request)
    {
         
    }

    public function updatevault($vaultId)
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
