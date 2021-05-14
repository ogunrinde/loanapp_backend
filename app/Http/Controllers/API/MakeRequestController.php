<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MakeRequest;
use App\SureVault;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserDetails;
use App\UserHomeAddress;
use App\ConnectBorrowerToLender;
use DB;

class MakeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $requestAmount = 1200;
        $minInterestRate = 6;
        $maxInterestRate = 12;
        $borrower_country_id = 160;
        $borrower_state_id = 2671;
        $borrower_city_id = 30978;
        $where = '';
       
        $matchingoffers = SureVault::where('maxRequestAmount', '>=', $requestAmount)
                                    ->where('minInterestperMonth', '<=',$maxInterestRate)
                                    ->with('user')->get();

        return $matchingoffers;               
        //array_search(needle, haystack)
    }

    public function peer(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'requestAmount' => 'required|numeric',
            'loanperiod' => 'required',
            'maxInterestRate' => 'required|numeric',
            'minInterestRate' => 'required|numeric',
            'repaymentplan' => 'required|string',
            'requiredcreditBereau' => 'required',
            'mobile' => 'required'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }


        $userdetails = UserDetails::with('user')->where(['mobile1' => $request->mobile])
                                    ->orwhere(['mobile2' => $request->mobile])->first();

        if($userdetails == null)
        {
            $error['message'] = "Lender Phone Number not Found";
            return response(['status' => 'failed', 'error'=> $error ]);
        }

        $userhomeaddress = UserHomeAddress::where(['user_id' => $userdetails->user_id])->first();

        $data['borrower_country_id'] = $userhomeaddress->country_id;
        $data['borrower_state_id'] = $userhomeaddress->state_id;
        $data['borrower_city_id'] = $userhomeaddress->city_id;
        $data['user_id'] = $request->user()->id;
        $data['request_type'] = 'peer to peer';


        $surevault = SureVault::where('user_id','=',$userdetails->user_id)->where('fundamount','>',$request->requestAmount)->first();

        if($surevault == null)
        {
            $error['message'] = "In Sufficient fund, Contact the Lender";
            return response()->json(['status' => 'failed', 'error'=>$error]);
        }

        DB::beginTransaction();

        try {

            $res = MakeRequest::Create($data);
            $toinsert = array('borrower_id' => $request->user()->id, 
                        'lender_id' => $surevault->user_id,
                        'sure_vault_id' => $surevault->id, 
                        'borrower_request_id' => $res->id, 
                        'status' => 'pending'); 
            ConnectBorrowerToLender::updateOrCreate(['borrower_request_id' => $res->id],$toinsert); 

            $makerequest = MakeRequest::where(['id' => $res->id])->first();
            
            $makerequest->requestStatus = 1;
            $makerequest->save();

            DB::commit();
                             

           return response(['status' => 'success', 'res' => $res]);

        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$errors]);
        }

    }

    public function getLendersForBorrower(Request $request)
    {
          $result = $this->processRequest($request);

          $res = MakeRequest::with('user')->where(['id' => $request->id])->first();

          return response(['status' => 'success', 'loanrequest' => $res, 'offers' => $result]);
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
            'requestAmount' => 'required|numeric',
            'loanperiod' => 'required',
            'maxInterestRate' => 'required|numeric',
            'minInterestRate' => 'required|numeric',
            'repaymentplan' => 'required|string',
            'requiredcreditBereau' => 'required',
            'borrower_country_id' => 'required|numeric',
            'borrower_state_id' => 'required|numeric',
            'borrower_city_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $data['user_id'] = $request->user()->id;
        $data['request_type'] = 'open request';
        //$data['lender_city_id'] = $request->lender_city_id;

        $res = MakeRequest::Create($data);

        $result = $this->processRequest($request);

       return response(['status' => 'success', 'loanrequest' => $res, 'offers' => $result]);
    }

    public function loanrequestandconnect(Request $request, $vaultId)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'requestAmount' => 'required|numeric',
            'loanperiod' => 'required',
            'maxInterestRate' => 'required|numeric',
            'repaymentplan' => 'required|string',
            'requiredcreditBereau' => 'required'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $vault = SureVault::where(['id' => $vaultId])->first();

        if($vault == null)
        {
            $error['message'] = "Unknown Vault";
            return response()->json(['status' => 'failed', 'error'=>$error]);  
        }

        if($request->requestAmount < $vault->minRequestAmount)
        {
            $error['message'] = "Request Amount does not Match with Lender Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        if($request->maxInterestRate < $vault->minInterestperMonth)
        {
            $error['message'] = "Request Amount does not Match with Lender Minimum Interest Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        if($request->requestAmount > $vault->maxRequestAmount)
        {
            $error['message'] = "Request Amount does not Match with Lender Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        if($vault->borrower_country_id != '' && $vault->borrower_country_id != $request->borrower_country_id)
        {
            $error['message'] = "Borrower Country does not Match with Lender Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        if($vault->borrower_state_id != '' && $vault->borrower_state_id != $request->borrower_state_id)
        {
            $error['message'] = "Borrower State does not Match with Lender Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        if($vault->borrower_city_id != '' && $vault->borrower_city_id != $request->borrower_city_id)
        {
            $error['message'] = "Borrower City does not Match with Lender Specifications";
            return response()->json(['status' => 'failed', 'error'=>$error]); 
        }

        $data['user_id'] = $request->user()->id;
        $data['request_type'] = 'open request';
        $data['borrower_country_id'] = $vault->borrower_country_id;
        $data['borrower_state_id'] = $vault->borrower_state_id;
        $data['borrower_city_id'] = $vault->borrower_city_id;
        $data['minInterestRate'] = $request->maxInterestRate;
        //$data['lender_city_id'] = $request->lender_city_id;

        $res = MakeRequest::Create($data);

        //$result = $this->processRequest($request);

       return response(['status' => 'success', 'loanrequest' => $res]);
    }

    public function processRequest($request)
    {
        $matchingoffers = SureVault::where('maxRequestAmount', '>=', $request->requestAmount)
                                    ->where('minInterestperMonth', '<=',$request->maxInterestRate)
                                    ->with('user')->get();
        return $matchingoffers;
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
       $connect =  ConnectBorrowerToLender::where(['borrower_request_id' => $id])->first();

       if($connect != null)
       {
         $error['message'] = "You are already connected to a lender, thus you can not delete this request";
         return response(['status' => 'failed', 'error' => $error]);
       }

       $data = MakeRequest::find($id)->delete();

       return response(['status' => 'success', 'request' => 'Request Deleted Successfully']);
    }
}
