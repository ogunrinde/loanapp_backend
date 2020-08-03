<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SureVault;
use Illuminate\Support\Facades\Validator;
use App\VaultWithdrawal;

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
            'borrower_state_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

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
