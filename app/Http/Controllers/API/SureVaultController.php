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

class SureVaultController extends Controller
{
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
            $error['message'] = "No request found from Borrower";
            return response(['status' => 'failed', 'error' => $error,'userdetails' => $userdetails]);
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
        $surevault = SureVault::Create($data); 

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
            'fundamount' => 'required|numeric',
            'availablefrom' => 'required|date',
            'availableto' => 'required|date|after_or_equal:availablefrom',
            'maxRequestAmount' => 'required|numeric',
            'minRequestAmount' => 'required|numeric',
            'minInterestperMonth' => 'required|numeric',
            'maxInterestperMonth' => 'required|numeric',
            'borrower_country_id' => 'required|numeric',
            'borrower_state_id' => 'required|numeric',
            'borrower_city_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }


        $data['email_must_be_verified'] = $request->email_must_be_verified == false ? false : true;
        $data['phonenumber_must_be_verified'] = $request->phonenumber_must_be_verified == false ? false : true;
        $data['bvn_must_be_verified'] = $request->bvn_must_be_verified == false ? false : true;
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
        //
    }
}
