<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConnectBorrowerToLender;
use Illuminate\Support\Facades\Validator;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\MakeRequest;
use DB;

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

        DB::beginTransaction();

        try {
            $makerequest = MakeRequest::where(['id' => $request->borrower_request_id])->first();

            if($makerequest == null)
            {
                 $error['message'] = 'Request not Found';
                  return response(['status' => 'failed', 'error' => $error]);
            }    

            //Check if borrower has connected to a lender with the same request id
            $connection = ConnectBorrowerToLender::where(['borrower_request_id' => $data['borrower_request_id']])->first(); 

            if($connection != null)
            {
                $error['message'] = 'You ve been Connected to a Lender before';
                return response(['status' => 'failed', 'error' => $error]);
            }

            if($request->user()->id == $request->lender_id)
            {
                $error['message'] = 'You can not connect to yourself';
                return response(['status' => 'failed', 'error' => $error]);
            }

            //get lender email
            $lender = User::where(['id' => $request->lender_id])->first();

            if($lender == null)
            {
                $error['message'] = 'Lender not Found';
                return response(['status' => 'failed', 'error' => $error]);
            }

            $data['user_id'] = $request->user()->id;
            $data['borrower_id'] = $request->user()->id;
            $data['status'] = 'pending';

            $res = ConnectBorrowerToLender::Create($data);


            //Update makerequest table set request Status to 1
            //1 means connected with lender
            $makerequest->requestStatus = 1;
            $makerequest->save();
            
            DB::commit();
            if(env('APP_ENV') != 'local')
                $this->mail("Loan Request",$lender->name,$lender->email);

            return response(['status' => 'success', 'connectRequest' => $res]);
        }catch(Exception $e)
        {
            DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$errors]);
        }
        
    }

    public function mail($subject,$name, $email)
    {
       $data = array("subject" => $subject, "name" => $name, "email" => base64_encode($email));
       Mail::to($email)->send(new Activitymail($data));
       
       return true;
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
