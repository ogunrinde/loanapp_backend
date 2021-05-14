<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SureVault;
use Illuminate\Support\Facades\Validator;
use App\VaultWithdrawal;
use App\ConnectBorrowerToLender;
use App\MakeRequest;
use App\UserDetails;
use App\UserHomeAddress;
use Config;
use DB;
use App\BankInformation;

class SureVaultController extends Controller
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
    public function index(Request $request)
    {
        $vault = SureVault::where(['user_id' =>$request->user()->id])->get();
        return response(['status' => 'success', 'vaults' => $vault]);
    }

    public function getvault($vaultId)
    {
        $vault = SureVault::find($vaultId)->with('user')->first();
        return response(['status' => 'success', 'vault' => $vault]);
    }

    public function gettransaction($surevaultId)
    {
        $transactions = VaultWithdrawal::where(['sure_vault_id' => $surevaultId])->with('request')->get();
         return response(['status' => 'success', 'transactions' => $transactions]);
    } 


    public function peerlendertoborrower(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'fundamount' => 'required|numeric',
            'availablefrom' => 'required|date',
            'availableto' => 'required|date|after_or_equal:availablefrom',
            'maxloantenor' => 'required|numeric',
            'minloantenor' => 'required|numeric',
            'minInterestperMonth' => 'required|numeric',
            'maxInterestperMonth' => 'required|numeric',
            'mobile' => 'required'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $userdetails = UserDetails::with('user')->where(['mobile1' => $request->mobile])
                                    ->orwhere(['mobile2' => $request->mobile])->first();


        if($userdetails == null)
        {
            $error['message'] = "Phone Number not Found";
            return response(['status' => 'failed', 'error'=> $error ]);
        }


        $homeaddress = UserHomeAddress::where(['user_id' => $userdetails->user->id])->first();

        if($homeaddress == null)
        {
            $error['message'] = "User is yet to complete registration";
            return response(['status' => 'failed', 'error'=> $error ]);
        }    


        $makerequest = MakeRequest::where('user_id', '=', $userdetails->user->id)
                                ->where('requestStatus','=', 0)
                                ->with('user')
                                ->orderBy('id', 'DESC')
                                ->first();


        if($makerequest == null)
        {
            $error['message'] = "No request found from Borrower, Kindly contact the Borrower";
            return response(['status' => 'failed', 'error' => $error,'userdetails' => $userdetails]);
        }    


        if($request->fundamount < $makerequest->requestAmount)
        {
            $error['message'] = "Cannot Process request. The Borrower needs NGN $makerequest->requestAmount not $request->fundamount";
            return response(['status' => 'failed', 'error' => $error]);
        }



        $connectwithborrower = ConnectBorrowerToLender::where('borrower_request_id', '=', $makerequest->id)
                                ->first();


        if($connectwithborrower != null)
        {
            $error['message'] = "Borrower is connect a Lender";
            return response(['status' => 'failed', 'error' => $error]);
        }  

        $data['borrower_country_id'] = $homeaddress->country_id;
        $data['borrower_state_id'] = $homeaddress->state_id;
        $data['borrower_city_id'] = $homeaddress->city_id;
        $data['maxRequestAmount'] = $request->fundamount;
        $data['minRequestAmount'] = $request->fundamount;
        $data['user_id'] = $request->user()->id;
        $data['bvn_must_be_verified'] = 0;
        $data['email_must_be_verified'] = 0;
        $data['phonenumber_must_be_verified'] = 0;
        $data['vault_creation_type'] = 'peer to peer';

        DB::beginTransaction();

        try {
          $surevault = SureVault::Create($data); 
          $makerequest->requestStatus = 1;
          $makerequest->save();

          $insert = array('lender_id'=> $request->user()->id, 'sure_vault_id' => $surevault->id,'connection_type' => 'lender connect','borrower_request_id' => $makerequest->id,'borrower_id' => $makerequest->user_id);

          $res = ConnectBorrowerToLender::Create($insert);
          DB::commit();
          return response(['status' => 'success']);
        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$errors]);
        }

        // $connect['user_id'] = $request->user()->id;
        // $connect['borrower_id'] = $userdetails->user->id;
        // $connect['status'] = 'pending';
        // $connect['lender_id'] = $request->user()->id;
        // $connect['sure_vault_id'] = $surevault->id;
        // $connect['borrower_request_id'] = $makerequest->id;


        // $res = ConnectBorrowerToLender::Create($connect);

        return response(['status' => 'success', 'request' => $makerequest]);

        
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
            'fundamount' => 'required|numeric|gt:0',
            'availablefrom' => 'required|date',
            'availableto' => 'required|date|after_or_equal:availablefrom',
            'maxRequestAmount' => 'required|numeric|gt:minRequestAmount',
            'minRequestAmount' => 'required|numeric',
            'minInterestperMonth' => 'required|numeric',
            'maxInterestperMonth' => 'required|numeric|gt:minInterestperMonth',
            'borrower_country_id' => 'required|numeric',
            'borrower_state_id' => 'required|numeric',
            'borrower_city_id' => 'required|numeric',
            'maxloantenor'=>'required|numeric|gt:minloantenor',
            'minloantenor'=>'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }


        $data['email_must_be_verified'] = $request->email_must_be_verified == false ? false : true;
        $data['phonenumber_must_be_verified'] = $request->phonenumber_must_be_verified == false ? false : true;
        $data['bvn_must_be_verified'] = $request->bvn_must_be_verified == false ? false : true;
        $data['creditbureau_report_required'] = $request->creditbureau_report_required == false ? false : true;
        $data['vault_creation_type'] = 'open vault';
        $data['user_id'] = $request->user()->id;

        $res = SureVault::Create($data);

       return response(['status' => 'success', 'surevaultcreation' => $res]);
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
        $connect =  ConnectBorrowerToLender::where(['sure_vault_id' => $id])->first();

       if($connect != null)
       {
         $error['message'] = "You are already connected to a borrower, thus you can not delete this vault";
         return response(['status' => 'failed', 'error' => $error]);
       }

       $data = SureVault::find($id)->delete();

       return response(['status' => 'success', 'request' => 'Vault Deleted Successfully']);
    }


    public function withdraw(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'vaultId' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }


       $response =  $this->generateRecipientcode($request);
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
        DB::beginTransaction();

        try{
            $vault = SureVault::find($request->vaultId);



            $withdrawn = (float)$result['data']['amount'] / 100;

            $vault->fundamount = (float)$vault->fundamount - $withdrawn;

            if($vault->fundamount < 0)
            {
                 $error['message'] = 'You cannot withdraw more than you have in Vault';
                 return response(['status' => 'failed', 'error' => $error]);
            }
            $vault->save();


            //Create Withdrawal
            $insert =  new VaultWithdrawal();
            $insert->Amount_withdrawn = $withdrawn;
            $insert->user_id = $request->user()->id;
            $insert->make_request_id = null;
            $insert->sure_vault_id = $request->vaultId;
            $insert->vault_withdrawal_type = "Withdrawal from Vault";
            $insert->transferInformation = json_encode($response);
            $res = $insert->save();

            //Get vault
            DB::commit();

            $vaults = SureVault::where(['user_id' =>$request->user()->id])->get();

            return response(['status' => 'success', 'VaultWithdrawal' => $res,'vaults'=>$vaults]);

        }
        catch(Exception $ex)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }

        return response()->json(['status' => 'success', 'message'=>'Request under Processing']);

    }

    public function generateRecipientcode($request)
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
          $data = $this->initiatePayment($request,$data);
        }
        return $data;
    }

    public function initiatePayment($req,$data)
    {
        $url = "https://api.paystack.co/transfer";
        $fields = [
          'source' => "balance",
          'amount' => $req->amount * 100,
          'recipient' => $data['recipient_code'],
          'reason' => "Withdraw fro Vault"
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
}
