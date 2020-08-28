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
            // 'lender_country_id' => 'required|numeric',
            // 'lender_state_id' => 'required|numeric'
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

        $data['lender_country_id'] = $userhomeaddress->country_id;
        $data['lender_state_id'] = $userhomeaddress->state_id;
        $data['lender_city_id'] = $userhomeaddress->city_id;
        $data['user_id'] = $request->user()->id;
        $data['request_type'] = 'peer to peer';

        $res = MakeRequest::Create($data);

        $surevault = SureVault::where('user_id', '=', $userdetails->user->id)
                                ->where('fundamount','>', 0)
                                ->with('user')->get();
       
        $res['peer'] = 1;                       

        return response(['status' => 'success', 'loanrequest' => $res, 'offers' => $surevault]);

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
            'lender_country_id' => 'required|numeric',
            'lender_state_id' => 'required|numeric'
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
        //
    }
}
