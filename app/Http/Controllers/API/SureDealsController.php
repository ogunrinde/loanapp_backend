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
            'Is_cash_disbursed' => 'required',
            'date_disbursed' => 'required|date',
            'mode_of_disbursement' => 'required',
            'Amount_disbursed' => 'required|numeric',
            'dealId' => 'required|numeric'
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

            //Create payment Schedules
            $makerequest = MakeRequest::where(['id' => $connection->borrower_request_id])->first();
            $surevault = Surevault::where(['id' => $connection->sure_vault_id])->first();
            $data = $this->repaymentstructure($makerequest,$surevault,$request->Amount_disbursed);
            $schedules = PaymentSchedules::insert($data);

            DB::commit();

            if(env('APP_ENV') != 'local')
                $this->mail('Sure Deals',$request->user()->name, $request->user()->email, $data);

            $request = suredeals::with(['borrower','lender','connect', 'request'])->where(['lender_id' => $request->user()->id,'Is_cash_disbursed' => '0'])->get();
            return response(['status' => 'success', 'VaultWithdrawal' => $res,'surevault'=>$vault, 'schedules' => $schedules]);
        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }  
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
