<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MakeRequest;
use App\SureVault;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserDetails;

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
        $matchingoffers = SureVault::with('user')
                                    ->where('maxRequestAmount', '>=', $requestAmount)
                                    ->where('minRequestAmount', '<=', $requestAmount)
                                    ->where('minInterestperMonth', '<=',$minInterestRate)
                                    ->where('maxInterestperMonth', '>=', $maxInterestRate)
                                    ->get();
        return $matchingoffers;                            
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
            'lender_country_id' => 'required|numeric',
            'lender_state_id' => 'required|numeric'
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

        $res = MakeRequest::Create($data);

        $surevault = SureVault::where('user_id', '=', $userdetails->user->id)
                                ->where('fundamount','>', 0)
                                ->get();

        return response(['status' => 'success', 'loanrequest' => $res, 'matchingoffers' => $surevault]);

    }

    public function getLendersForBorrower(Request $request)
    {
          $result = $this->processRequest($request);

          return response(['status' => 'success', 'loanrequest' => null, 'matchingoffers' => $result]);
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

        $res = MakeRequest::Create($data);

        $result = $this->processRequest($request);

       return response(['status' => 'success', 'loanrequest' => $res, 'matchingoffers' => $result]);
    }

    public function processRequest($request)
    {
        $matchingoffers = SureVault::where('maxRequestAmount', '>=', $request->requestAmount)
                                    ->where('minRequestAmount', '<=', $request->requestAmount)
                                    ->where('minInterestperMonth', '<=',$request->minInterestRate)
                                    ->where('maxInterestperMonth', '>=', $request->maxInterestRate)
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
