<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConnectBorrowerToLender;
use Illuminate\Support\Facades\Validator;

class ConnectBorrowerToLenderController extends Controller
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
            'lender_id' => 'required|numeric',
            'sure_vault_id' => 'required|numeric',
            'borrower_request_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        //Check if borrower has connected to a lender with the same request id
        $connection = ConnectBorrowerToLender::where(['borrower_request_id' => $data['borrower_request_id']])->first(); 

        if($connection != null)
        {
            $error['message'] = 'You ve been Connected to a Lender before';
            return response(['status' => 'failed', 'error' => $error]);
        }

        $data['user_id'] = $request->user()->id;
        $data['borrower_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $res = ConnectBorrowerToLender::Create($data);

       return response(['status' => 'success', 'connectRequest' => $res]);
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
