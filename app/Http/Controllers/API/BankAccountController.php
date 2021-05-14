<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\BankInformation;
use App\BankCode;
use App\UserDetails;
use Config;

class BankAccountController extends Controller
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
                'bvn' => 'required|numeric',
                'bankname' => 'required',
                'accountnumber' => 'required|numeric'
            ]);

            if($validator->fails()) { 
                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
            }



            // $status = $this->resolveBVN($request);
            // // return response(['status' => 'failed', 'message' => $status, 'message' => $status]);

            // if($status['status'] == false)
            // {
            //     $error['message'] = $status['response']['message'];
            //     return response(['status' => 'failed', 'error' => $error, 'message' => $status]);
            // }

            // if($status['firstname'] == 0 || $status['lastname'] == 0)
            // {
            //     $msg = $status['firstname'] == 0 ? 'Firstname does not match BVN data ' : '';
            //     $msg .= $status['lastname'] == 0 ? ' Surname does not match BVN data' : '';
            //     return response(['status' => 'failed', 'error' => $msg, 'message' => $status]);
            // }    

            $bank = BankCode::where(['bankname' => $request->bankname])->first();

            $data['user_id'] = $request->user()->id;
            $data['bankcode'] = $bank->bankcode;
            $data['Is_BVN_verified'] = 1;

            $res = BankInformation::updateOrCreate(['user_id' => $request->user()->id],$data);

           return response(['status' => 'success', 'bankdetails' => $res]);
    }

    public function resolveBVN(Request $request)
    {
        $data = array('firstname' => 0,'lastname' => 0, 'mobile' => 0, 'status' => false, 'response' => '');
        $curl = curl_init();
  
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/bank/resolve_bvn/".$request->bvn."",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: Bearer ".$this->API_SECRET."",
              "Cache-Control: no-cache",
            ),
          ));
          
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);
          
          if ($err) {
            return $data;
          } else {
             $result = json_decode($response, true);
             $data['response'] = $result;
             $resp = $this->confirm($result,$request,$data);
             return $resp;
          }
    }

    public function confirm($result,$request,$data)
    {
        
        if($result['status'] == true)
        {
            $data['status'] = true;
            $details = UserDetails::where(['user_id' => $request->user()->id])->first();
            if($details == null) return false;
            if(isset($result['data']['first_name']) && $result['data']['first_name'] == $details->firstname) 
                $data['firstname'] = 1;
            if(isset($result['data']['last_name']) && $result['data']['last_name'] == $details->surname)
                $data['surname'] = 1;

            
        }
        return $data;
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
