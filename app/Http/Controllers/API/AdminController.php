<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\userroles;
use App\SureVault;
use App\MakeRequest;
use Illuminate\Support\Facades\Validator;
use App\suredeals;
use App\PaymentSchedules;

class AdminController extends Controller
{

    public function index(Request $request)
    {
    	$admins = User::where('userType','=', 'admin')->orwhere('userType','=','subadmin')->get();
    	return response(['status' => 'success', 'admins' => $admins]);
    }

    public function store(Request $request)
    {
    	$data = $request->all();
		$validator = Validator::make($data, [
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users'
	    ]);

	    if($validator->fails()) { 
	                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
	    }

	    $data['password'] = bcrypt($request->email);
	    $data['userType'] = 'subadmin';


	    $user = User::create($data);


	    return response(['status' => 'success', 'user' => $user, 'userType' => 'subadmin']);
    }

    public function userrole(Request $request)
    {
    	$data = $request->all();
		$validator = Validator::make($data, [
            'role_id' => 'required|numeric',
            'user_id' => 'required|numeric'
	    ]);

	    if($validator->fails()) { 
	                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
	    }

	    $data['role_id'] = $request->role_id;
	    $data['user_id'] = $request->user_id;

	    $res = userroles::updateOrCreate(["user_id" => $request->user_id],$data);

	    return response(['status' => 'success']);
    }

    public function getuserrole(Request $request, $id)
    {
    	$res = userroles::where(["user_id" => $id])->first();
    	return response(['status' => 'success', 'userrole' => $res]);
    }

    public function users(Request $request)
    {
    	$res = User::where('userType','=', null)->get();
    	return response(['status' => 'success', 'users' => $res]);
    }

    public function getallvault(Request $request)
    {
    	$vault = SureVault::all();
        return response(['status' => 'success', 'vaults' => $vault]);
    }

    public function getallloansrequest(Request $request)
    {
    	$requests = MakeRequest::with('connect')->get();
        return response()->json(['status' => 'success', 'requests' => $requests]);
    }

    public function getdeal(Request $request, $id)
    {
    	$deal = suredeals::where(['lender_borrower_connection_id' => $id])->first();
    	return response()->json(['status' => 'success', 'deal' => $deal]);
    }

    public function paymentschedules(Request $request, $id)
    {
        $schedules = PaymentSchedules::with(['borrower','lender', 'request'])->where(['borrower_request_id' => $id])->get();
        return response()->json(['status' => 'success', 'schedules'=>$schedules]);
    }

    public function activitiesanalytics(Request $request)
    {
        $surevault = SureVault::all();
        $loanrequest = MakeRequest::all();
        $suredeals = suredeals::where(['Is_cash_disbursed' => '1'])->get();
        $users = User::where('userType','=', null)->get();

        $fundamount = 0;
        $makerequest = 0;
        $disbursed = 0;

        for($r = 0; $r < count($surevault); $r++)
        {
        	$fundamount = $surevault[$r]->fundamount + $fundamount;
        }

        for($r = 0; $r < count($loanrequest); $r++)
        {
        	$makerequest = $loanrequest[$r]->requestAmount + $makerequest;
        }

        for($r = 0; $r < count($suredeals); $r++)
        {
        	$disbursed = $suredeals[$r]->Amount_disbursed + $suredeals;
        }

        $res = array("suredeals" => $suredeals, "users" => $users->count(), "disbursed" => $disbursed, "loanrequest" => $makerequest, "surevault" => $fundamount);

        return response(['status' => 'success', "analytics" => $res]);
    }

    public function deactivate(Request $request, $id)
    {
       $user = User::find($id);

       if($user == null)
       {
          $error['message'] = "User not Found";
          return response(['status' => 'failed', "error" => $error]);
       }
       $user->deactivated = 1;
       $user->deactivated_at = date("Y-m-d H:i:s");
       $user->deactivated_by = $request->user()->id;
       $user->save();

       return response(['status' => 'success', "message" => "User deactivated Successfully"]);
    }

   

}
