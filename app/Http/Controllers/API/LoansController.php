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
use Config;
use App\BankInformation;
use App\UserDetails;
use App\Repayments;
use App\PaymentSchedules;

class LoansController extends Controller
{
    public $API_SECRET;

    public function __construct()
    {
      $this->API_SECRET = Config::get('app.api_secret.key');
    }


    public function getLenderpendingloanapprovals(Request $request)
    {
    	$request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id, 'connection_type' => null,'status' => 'pending'])->get();
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
                'LoanID' => uniqid(),
                'PaymentStatus' => 'Pending'
            );
            $res = suredeals::updateOrCreate(['lender_borrower_connection_id' => $request->connectId],$data);

            if(config('app.env') != 'local')
            {
                
                $user = User::where(['id' => $request->borrowerId])->first();
                $this->mail("Loan Approved", $user->name, $user->email,"approved");
            }      
            
         }else {

            $makerequest = MakeRequest::where(['id'=> $request->requestId])->first();
            $makerequest->status = 0;
            $makerequest->save();

            $connect = ConnectBorrowerToLender::find($request->connectId);
            $connect->delete();

            if(config('app.env') != 'local')
            {
                $user = User::where(['id' => $request->borrowerId])->first();
                $this->mail("Loan Declined", $user->name, $user->email,"declined");
            } 
         }

          $request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['lender_id' => $request->user()->id,'status' => 'pending'])->get();

          return response()->json(['status' => 'success', 'request'=>$request,'suredeal'=>$res]);

    }

    public function approved()
    {

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

            $makerequest = MakeRequest::where('id','=',$request->borrower_request_id)->first();
            $surevault = SureVault::where('user_id','=',$request->user()->id)->where('fundamount','>',$makerequest->requestAmount)->first();

            if($surevault == null)
            {
                $error['message'] = "You don't have an active vault, Kindly fund your Vault";
                return response()->json(['status' => 'failed', 'error'=>$error]);
            }

            if($makerequest->requestAmount > $surevault->maxRequestAmount)
            {
                $error['message'] = "The Maximum amount you can lend is $surevault->maxRequestAmount as stated in your vault configuration";
                return response()->json(['status' => 'failed', 'error'=>$error]);
            }

            if($makerequest->requestAmount < $surevault->minRequestAmount)
            {
                $error['message'] = "The Minimum amount you can lend is $surevault->minRequestAmount as stated in your vault configuration";
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
            $data['connection_type'] = 'lender connect';
            

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

    public function borrowerwithdrawcash(Request $request, $id)
    {

        $req = MakeRequest::where(['id' => $id, 'user_id' => $request->user()->id])->first();
        if($req == null)
        {
               $error['message'] = 'Request not Found';
               return response(['status' => 'failed', 'error' => $error]);
        }

        $connect = ConnectBorrowerToLender::where(['borrower_request_id' => $id])->first();

        if($connect == null)
        {
               $error['message'] = 'Connection not Found';
               return response(['status' => 'failed', 'error' => $error]);
        }

        $deal = suredeals::where(['lender_borrower_connection_id' => $connect->id])->first();


        if($deal == null)
        {
               $error['message'] = 'Withdrawal not Permitted';
               return response(['status' => 'failed', 'error' => $error]);
        }

        if($deal->Is_cash_disbursed == 0)
        {
            $error['message'] = 'Cash is not yet disbursed';
            return response(['status' => 'failed', 'error' => $error]);
        }

        if($deal->Has_borrower_withdraw_cash == 1)
        {
            $error['message'] = 'You have already withdrawn the cash';
            return response(['status' => 'failed', 'error' => $error]);
        }

       $amounttopay = $deal->Amount_disbursed * 100;  
       $reason = 'Borrowed';

       $response =  $this->generateRecipientcode($amounttopay,$request, $reason);
       //return response(['status' => 'failed', 'error' => $response]);

       if($response['generatecodestatus'] == false)
       {
          $error['message'] = isset($response['response']['message']) ? $response['response']['message'] : 'Transfer failed, while generating Code';
          return response(['status' => 'failed', 'error' => $error, 'info' => $response]);
       }

       if($response['transferstatus'] == false)
       {
          $error['message'] = isset($response['transferInformation']['message']) ? $response['transferInformation']['message'] : 'Transfer failed';
          return response(['status' => 'failed', 'error' => $error, 'info' => $response]);
       }



        //Process Withdrawal call paystack to transfer money to user account

        $deal->borrowertransfermessage = $response['transferInformation']['message'];
        $deal->borrowertransferInformation = json_encode($response['transferInformation']);
        $deal->Has_borrower_withdraw_cash = 1;
        $deal->date_borrower_withdraw = date('Y-m-d');
        $deal->save();

        return response()->json(['status' => 'success', 'message'=>'Request under Processing']);

    }

    public function generateRecipientcode($amounttopay,$request, $reason)
    {
        $bankinfo = BankInformation::where(['user_id' => $request->user()->id])->first();
        $details = UserDetails::where(['user_id' => $request->user()->id])->first();
        $name = $details->surname.' '.$details->firstname;
        $url = "https://api.paystack.co/transferrecipient";
        $fields = [
          'type' => "nuban",
          'name' => $name,
          'account_number' => $bankinfo->accountnumber,
          'bank_code' => $bankinfo->bankcode,
          'currency' => "NGN"
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer ".$this->API_SECRET."",
          "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        $data = array('generatecodestatus' =>false, 'transferstatus' => false, 'recipient_code'=> '', 'transferInformation' => '', 'finaltransferInformation' => '', 'generatecode_response' => $response, 'transfer_response' => '');
        if($response != null && $response['status'] == true)
        {
          $data['generatecodestatus'] = true;
          $data['recipient_code'] = $response['data']['recipient_code'];
          $data = $this->initiatePayment($amounttopay,$data, $reason);
        }
        return $data;
    }

    public function initiatePayment($amounttopay,$data, $reason)
    {
        $url = "https://api.paystack.co/transfer";
        $fields = [
          'source' => "balance",
          'amount' => $amounttopay,
          'recipient' => $data['recipient_code'],
          'reason' => $reason
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer ".$this->API_SECRET."",
          "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        $data['transferInformation'] = $response;
        if($response['status'] == true)
        {
          $data['transferstatus'] = true;
        }
        return $data;
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

            $makerequest = MakeRequest::where(['id'=> $request->requestId])->first();
            $makerequest->status = 0;
            $makerequest->save();

            $connect = ConnectBorrowerToLender::find($request->connectId);
            $connect->delete();
            
            if(env('APP_ENV') != 'local')
            {
                $user = User::where(['id' => $request->lenderId])->first();
                $this->mail("Loan Offer Declined", $user->name, $user->email,"loan offer declined");
            } 
         }

          $request = ConnectBorrowerToLender::with(['borrower','request','surevault'])->where(['borrower_id' => $request->user()->id,'status' => 'pending'])->get();

          return response()->json(['status' => 'success', 'request'=>$request,'suredeal'=>$res]);

    }


    public function borrowermadepayment(Request $request, $requestId)
    {
        //Insert into Repayment and update Payment schedule table
         DB::beginTransaction();

        try{

          $result = $this->verify($request->reference);

          if(!isset($result['status']) || $result['status'] != 'success')
          {
              $error['message'] = "Cash Transfer can not be verify";
              return response()->json(['status' => 'failed', 'error'=>$error]);
              //send mail
          }

          $req = ConnectBorrowerToLender::where(['borrower_request_id' => $requestId])->first();

          if($req == null)
          {
            $error['message'] = 'Request not Found';
            return response()->json(['status' => 'failed', 'error'=>$error]);
          }

          $data['amount_paid'] = (float)$result['data']['amount'] / 100;
          $data['PaymentStatus'] = $result['status'];
          $data['is_confirmed'] = $result['status'] == 'success' ? 1 : 0;
          $data['mode_of_payment'] = 'Paystack Transfer';
          $data['transferInformation'] = json_encode($result);
          $data['date_paid'] = date('Y-m-d');
          $data['borrower_id'] = $req->borrower_id;
          $data['lender_id'] = $req->lender_id;
          $data['borrower_request_id'] = $requestId;


          $repay = Repayments::Create($data);          

          $schedule = PaymentSchedules::where(['borrower_request_id' => $requestId, 'status' => 'pending'])->get();

          $period = (float)$data['amount_paid'] / (float)$schedule[0]->expected_amount_to_paid; 

          $overdue = [];
          $amountoverdue = 0;

          for($f = 0; $f < count($schedule); $f++)
          {
              if(strtotime($schedule[$f]->dueDate) <= strtotime(date('Y-m-d')))
              {
                $overdue[] = $schedule[$f];
                $amountoverdue =  $schedule[$f]->expected_amount_to_paid + $amountoverdue;
                $schedule[$f]->status = 'Paid';
                $schedule[$f]->save();
              }
          }

          DB::commit();

          return response()->json(['status' => 'success', 'message' => 'Transfer Successful']);
        }
        catch(Exception $ex)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }
    }

    public function verify($reference)
    {
        $url = 'https://api.paystack.co/transaction/verify/'.$reference;
        //open connection
        $ch = curl_init();
        //set request parameters 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$this->API_SECRET.'']);

        //send request
        $request = curl_exec($ch);
        //close connection
        curl_close($ch);
        //declare an array that will contain the result
        $result = array();
        if($request)
        $result = json_decode($request, true);
        return $result;
    }

    public function lenderwithdrawcash(Request $request, $id)
    {

        $repayments = [];
        if(isset($request->all) &&  $request->all == 1)
        {
          $repayments = Repayments::where(['IsWithdrawn' => 0,'borrower_request_id' => $id, 'lender_id' => $request->user()->id])->get
          ();
        }
        else if(isset($request->all) &&  $request->all == 0)
        {
          $repayments = Repayments::where(['IsWithdrawn' => 0,'borrower_request_id' => $id, 'lender_id' => $request->user()->id, 'id' => $request->repaymentId])->get();
        }

        if(count($repayments) == 0)
        {
           $error['message'] = 'No Pending Withdrawal';
           return response(['status' => 'failed', 'error' => $error]);
        }

        //return response()->json(['status' => 'success', 'message'=>$repayments->sum('amount_paid')]);

        $id = $repayments->pluck('id');
        $amount_paid = $repayments->sum('amount_paid');

      
        

       $amounttopay = $amount_paid * 100;  
       $reason = 'Withdraw Returned Money';

       $response =  $this->generateRecipientcode($amounttopay,$request, $reason);
       //return response(['status' => 'failed', 'error' => $response]); 


       if($response['generatecodestatus'] == false)
       {
          $error['message'] = isset($response['response']['message']) ? $response['response']['message'] : 'Transfer failed, while generating Code';
          return response(['status' => 'failed', 'error' => $error, 'info' => $response]);
       }

       if($response['transferstatus'] == false)
       {
          $error['message'] = isset($response['transferInformation']['message']) ? $response['transferInformation']['message'] : 'Transfer failed';
          return response(['status' => 'failed', 'error' => $error, 'info' => $response]);
       }



        //Process Withdrawal call paystack to transfer money to user account

        $update = Repayments::whereIn('id', $id)
                  ->update([
                        'lendertransfermessage' => $response['transferInformation']['message'],
                        'lendertransferInformation' => json_encode($response['transferInformation']),
                        'DateWithdrawn' => date('Y-m-d'),
                        'WithdrawnType' => 'Paystack Transfer',
                        'IsWithdrawn' => 1
                  ]);
       

        return response()->json(['status' => 'success', 'message'=>'Request under Processing', 'repayments' => $repayments]);

    }

   

   
}
