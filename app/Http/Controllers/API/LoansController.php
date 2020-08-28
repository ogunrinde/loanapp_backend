<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConnectBorrowerToLender;
use App\suredeals;
use App\SureVault;
use App\MakeRequest;
use Illuminate\Support\Facades\Validator;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;
use DB;

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

            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->borrowerId])->first();
                $this->mail("Loan Approved", $user->name, $user->email,"approved");
            }      
            
         }else {
            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->borrowerId])->first();
                $this->mail("Loan Declined", $user->name, $user->email,"declined");
            } 
         }

          $request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id,'status' => 'pending'])->get();

          return response()->json(['status' => 'success', 'request'=>$request,'suredeal'=>$res]);

    }

    public function mail($subject,$name, $email, $type)
    {
       $data = array("subject" => $subject, "name" => $name , "email" => base64_encode($email), "type" => $type, "for" => $request->user()->name);
       Mail::to($email)->send(new Activitymail($data));
       
       return true;
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
         $offers = SureVault::with('user')->where('fundamount','>', 0)
                                          ->where('vault_creation_type','=','open vault')
                                          ->get();
         return response()->json(['status' => 'success', 'offers'=>$offers]);
    }

    public function sureborroweroffers(Request $request)
    {
         $requests = MakeRequest::with('user')->where(['requestStatus' => 0, 'request_type' => 'open request'])->get();
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


        DB::beginTransaction();

        try {
            $surevault = SureVault::where('user_id','=',$request->user()->id)->where('fundamount','>',0)->orderBy('id', 'DESC')->first();

            if($surevault == null)
            {
                $error['message'] = "You don't have an active vault";
                return response()->json(['status' => 'failed', 'error'=>$error]);
            }

            $connect = ConnectBorrowerToLender::where('borrower_request_id', '=', $request->borrower_request_id)->first();

            if($connect != null)
            {
                $error['message'] = "A lender has already connected to this borrower";
                return response()->json(['status' => 'failed', 'error'=>$error]);
            }    


            $data['lender_id'] = $request->user()->id;
            $data['sure_vault_id'] = $surevault->id;

            $res = ConnectBorrowerToLender::updateOrCreate(['borrower_request_id' => $request->borrower_request_id],$data);


            $makerequest = MakeRequest::where(['id' => $request->borrower_request_id])->first();
            //Update makerequest table set request Status to 1
            //1 means connected with lender
            $makerequest->requestStatus = 1;
            $makerequest->save();

            DB::commit();

            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->borrower_id])->first();
                $this->mail("Loan Connect", $user->name, $user->email,"lender connect");
            } 
        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$errors]);
        }    

       return response(['status' => 'success', 'connect' => $res]);
    }

    public function getborrowersLoansReceived(Request $request)
    {
        $request = suredeals::with(['borrower','lender','connect', 'request'])->where(['borrower_id' => $request->user()->id,'Is_cash_disbursed' => '1'])->get();
        return response()->json(['status' => 'success', 'request'=>$request]);
    }

    public function updateofferrequeststatus(Request $request)
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
                'lender_id' => $request->lenderId, 
                'borrower_id' => $request->user()->id,
                'request_id' => $request->requestId,
                'LoanID' => uniqid()
            );
            $res = suredeals::updateOrCreate(['lender_borrower_connection_id' => $request->connectId],$data);

            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->lenderId])->first();
                $this->mail("Loan Offer Accepted", $user->name, $user->email,"loan offer accepted");
            }      
            
         }else {
            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->lenderId])->first();
                $this->mail("Loan Offer Declined", $user->name, $user->email,"loan offer declined");
            } 
         }

          $request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['borrower_id' => $request->user()->id,'status' => 'pending'])->get();

          return response()->json(['status' => 'success', 'request'=>$request,'suredeal'=>$res]);

    }

   

   
}
