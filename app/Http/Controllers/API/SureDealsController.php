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
use App\MakeRequest;
use App\PaymentSchedules;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;
use Config;

class SureDealsController extends Controller
{

    public $API_SECRET;

    public function __construct()
    {
      $this->API_SECRET = Config::get('app.api_secret.key');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function mail($subject,$name, $email, $disburse)
    {
       $data = array("subject" => $subject, "name" => $name, "disburse" => $disburse, "email" => base64_encode($email));
       Mail::to($email)->send(new Activitymail($data));
       
       return true;
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
            'Amount_disbursed' => 'required|numeric',
            'dealId' => 'required|numeric',
            'connectionId' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        DB::beginTransaction();

        try{
            //Get Deal
            $deal = suredeals::where(['id' => $request->dealId])->first();

            if($deal == null)
            {
                $error['message'] = "Deal not Found";
                return response()->json(['status' => 'failed', 'error'=>$error]);  
            }

            if($deal->Is_cash_disbursed == 1)
            {
                $error['message'] = "Cash is already disbursed";
                return response()->json(['status' => 'failed', 'error'=>$error]);  
            }

            $result = $this->verify($request->reference);

            

            if(!isset($result['status']) || $result['status'] != 'success')
            {
                $error['message'] = "Cash Transfer can not be verify";
                return response()->json(['status' => 'failed', 'error'=>$error]);
                //send mail
            }
            $deal->Is_cash_disbursed = '1';
            $deal->date_disbursed = date('Y-m-d');
            $deal->mode_of_disbursement = 'Paystack Transfer';
            $deal->Amount_disbursed = (float)$result['data']['amount'] / 100;
            $deal->verifyresponse = json_encode($result);
            $deal->PaymentStatus = $result['data']['status'];
            $deal->save();

            //Create Withdrawal Table
            $connection = ConnectBorrowerToLender::find($request->connectionId);

            if($connection != null)
            {
                $data = array(
                "Amount_withdrawn" => $request->Amount_disbursed,
                "user_id" => $request->user()->id,
                "make_request_id" => $connection->borrower_request_id,
                "sure_vault_id"=> $connection->sure_vault_id,
                "vault_withdrawal_type" => "transfer to borrower"
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

            //Create payment Schedules
            $makerequest = MakeRequest::where(['id' => $connection->borrower_request_id])->first();
            $surevault = Surevault::where(['id' => $connection->sure_vault_id])->first();
            $data = $this->repaymentstructure($makerequest,$surevault,$request->Amount_disbursed);
            $schedules = PaymentSchedules::insert($data);

            DB::commit();

             if(config('app.env') != 'local')
                $this->mail('Sure Deals',$request->user()->name, $request->user()->email, $data);

            $deals = suredeals::with(['borrower','lender','connect', 'request'])->where(['lender_id' => $request->user()->id,'Is_cash_disbursed' => '0'])->get();
            return response(['status' => 'success', 'VaultWithdrawal' => $res,'surevault'=>$vault, 'schedules' => $schedules, 'request' => $deals]);
        }catch(Exception $e)
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

    public function repaymentstructure($makerequest,$surevault,$amount)
    {

        $minloantenor = $surevault->minloantenor;
        $interestperMonth = ((float)$surevault->minInterestperMonth / 100) * (float)$amount;
        $totalInterestonloan = $interestperMonth * $minloantenor;
        $totalamounttorepay = $amount + $totalInterestonloan;
        $repaymentplan = $makerequest->repaymentplan;
        $res = [];
        if(strtolower($repaymentplan) == 'daily')
        {
            $total = $minloantenor * 30; //30 days make a month
            $pay = (float)$totalamounttorepay / $total;
            $iter = 1;
        }
        else if(strtolower($repaymentplan) == 'weekly')
        {
             $total = $minloantenor * 4; //how many week to pay
             $pay = (float)$totalamounttorepay / $total;
             $iter = 7;
        }
        else if(strtolower($repaymentplan) == 'monthly')
        {
             $total = $minloantenor; //how many months
             $pay = (float)$totalamounttorepay / $total;
             $iter = 30;
        }
        for($r = 0; $r < $total; $r++)
        {
           $f = $iter * ($r+1); 
           $date = strtotime("+ ".$f." day");
           $data = array(
                    'borrower_id' =>$makerequest->user_id,
                    'lender_id' =>$surevault->user_id,
                    'borrower_request_id' =>$makerequest->id,
                    'expected_amount_to_paid' => $pay,
                    'dueDate' =>date('Y-m-d',$date),
                    'status' =>'pending');   
           $res[] = $data;
        }
        return $res;
        //return response()->json(['status' => 'failed', 'error'=>$totalamounttorepay,'pay'=>$pay]);

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
