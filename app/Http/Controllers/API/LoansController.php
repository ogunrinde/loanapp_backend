<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConnectBorrowerToLender;
use App\suredeals;
use App\SureVault;
use App\MakeRequest;
use Illuminate\Support\Facades\Validator;

class LoansController extends Controller
{
    public function getLenderpendingloanapprovals(Request $request)
    {
    	$request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id,'status' => 'pending'])->get();
    	return response()->json(['status' => 'success', 'request'=>$request]);
    }

    public function getLenderapprovedLoan(Request $request)
    {
    	$approvedLoans = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id, 'status' => 'approved'])->get();
    	return response()->json(['status' => 'success', 'request'=>$approvedLoans]);
    }

    public function getBorrowerpendingloanapprovals(Request $request)
    {
    	$request = ConnectBorrowerToLender::with(['lender','request', 'surevault'])->where(['borrower_id' => $request->user()->id, 'status' => 'pending'])->get();
    	return response()->json(['status' => 'success', 'request'=>$request]);
    }

    public function getBorrowerapprovedloan(Request $request)
    {
        $request = ConnectBorrowerToLender::with(['lender','request', 'surevault'])->where(['borrower_id' => $request->user()->id,'status' => 'approved'])->get();
        return response()->json(['status' => 'success', 'request'=>$request]);
    }

    public function updateloanapprovalstatus(Request $request)
    {
         $connect = ConnectBorrowerToLender::find($request->connectId);
         if($connect == null)
         {
             $error['message'] = 'Request not Found';
             return response(['status' => 'failed', 'error' => $error]);
         }

         $connect->status = $request->status;
         $connect->save();

         $res = array();

         if($request->status == 'approved')
         {
            $data = array(
                "lender_borrower_connection_id" =>$request->connectId, 
                'lender_id' => $request->user()->id, 
                'borrower_id' => $request->borrowerId,
                'request_id' => $request->requestId,
                'LoanID' => uniqid()
            );
            $res = suredeals::updateOrCreate(['lender_borrower_connection_id' => $request->connectId],$data);
            
         }

          $request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id,'status' => 'pending'])->get();
          return response()->json(['status' => 'success', 'request'=>$request,'suredeal'=>$res]);

    }

    public function getLoanToBeDisbursed(Request $request)
    {
        $request = suredeals::with(['borrower','lender','connect', 'request'])->where(['lender_id' => $request->user()->id,'Is_cash_disbursed' => '0'])->get();
        return response()->json(['status' => 'success', 'request'=>$request]);
    }
    public function getLoanseDisbursed(Request $request)
    {
        $request = suredeals::with(['borrower','lender','connect', 'request'])->where(['lender_id' => $request->user()->id,'Is_cash_disbursed' => '1'])->get();
        return response()->json(['status' => 'success', 'request'=>$request]);
    }

    public function surelenderoffers(Request $request)
    {
         $offers = SureVault::with('user')->get();
         return response()->json(['status' => 'success', 'offers'=>$offers]);
    }

    public function sureborroweroffers(Request $request)
    {
         $requests = MakeRequest::with('user')->get();
         return response()->json(['status' => 'success', 'requests'=>$requests]);
    }
    public function connectwithborrower(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'borrower_request_id' => 'required|numeric',
            'borrower_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $surevault = SureVault::where(['user_id'=>$request->user()->id])->orderBy('id', 'DESC')->first();

        if($surevault == null)
        {
            $error['message'] = "You don't have an active vault";
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }

        $connect = ConnectBorrowerToLender::where('borrower_request_id', '=', $request->borrower_request_id)
                                            ->orwhere('status','=', 'pending')
                                            ->orwhere('status','=', 'approved')
                                            ->first();

        if($connect != null)
        {
            $error['message'] = "A lender has already connected to this borrower";
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }    
        $data['lender_id'] = $request->user()->id;
        $data['sure_vault_id'] = $surevault->id;

        $res = ConnectBorrowerToLender::Create(['borrower_request_id' => $request->borrower_request_id],$data);

       return response(['status' => 'success', 'connect' => $res]);
    }

   

   
}
