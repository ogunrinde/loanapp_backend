<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;
use App\UserDetails;
use App\Verification;
use App\userroles;
use App\permissions;
use App\roles;
use DB;

class AuthController extends Controller
{

	public function register(Request $request)
	{
		$data = $request->all();
		$validator = Validator::make($data, [
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
	    ]);

	    if($validator->fails()) { 
	                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
	    }

	    $data['password'] = bcrypt($request->password);
	    $data['userType'] = null;
	    $data['active'] = 1;

	    $user = User::create($data);

	    $accessToken = $user->createToken('authToken')->accessToken;

	    if(env('APP_ENV') != 'local')
			$this->mail("Account Created", $request->name, $request->email);

	    return response(['status' => 'success', 'user' => $user, 'access_token' => $accessToken]);
	}

	public function adminregister(Request $request)
	{
		$data = $request->all();
		$validator = Validator::make($data, [
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
	    ]);

	    if($validator->fails()) { 
	                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
	    }

	    $admin = User::where(['userType' => 'admin'])->first();

	    if($admin != "")
	    {
	    	$error['message'] = "An Admin is already Created";
	    	return response()->json(['status' => 'failed', 'error'=> $error]);
	    }

	    DB::beginTransaction();
	    try{
	    	$data['password'] = bcrypt($request->password);
		    $data['userType'] = 'admin';

	   
		    $user = User::create($data);

		    $role['name'] = 'Admin';
		    $role = roles::Create($role);
		    $this->createpermission($role);

		    $userrole['role_id'] = $role->id;
		    $userrole['user_id'] = $user->id;
		    userroles::updateOrCreate(["user_id" => $request->user_id],$userrole);

		    $accessToken = $user->createToken('authToken')->accessToken;
		    DB::commit();
		    return response(['status' => 'success', 'user' => $user, 'access_token' => $accessToken, 'userType' => 'admin']);

	    }catch(Exception $e)
	    {
	    	DB::rollback();
            $error['message'] = $e;
            return response()->json(['status' => 'failed', 'error'=>$error]);
	    }

	    
	}

	public function createpermission($role)
	{
		$res = [];
		$res = ['Loan Request','Repayment','Vault','Loan Request','Surebanker User','User Management'];
		for ($r = 0; $r < count($res);$r++) {
    		$values = array("role_id" => $role->id, "doc_type" => $res[$r], "create" => 1, "approval" => 1, "view" => 1, "edit" => 1, "cancel" => 1, "deactivate" => 1);
    		$response = permissions::updateOrCreate(['role_id' => $role->id, 'doc_type' => $res[$r]],$values);
    	}
    	return true;
	}

	public function adminlogin(Request $request)
    {

        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
        	//$error['message'] = "Invalid Credentials";
            return response(['status' => 'failed', 'message' => 'Invalid Credentials']);
        }

        // if(auth()->user()->userType != 'admin')
        // {
        // 	return response(['status' => 'failed', 'message' => 'Unauthorized User']);
        // }

        $role = userroles::where(['user_id' => $request->user()->id])->first();

        if($role == null && $request->user()->userType == 'subadmin')
        {
        	return response(['status' => 'failed', 'message' => 'Unassign User, Contact Admin to Assign Role to You']);
        }



        $permissions = permissions::where(['role_id' => $role->role_id])->get();

        if(count($permissions) == 0)
        {
        	return response(['status' => 'failed', 'message' => 'Unauthorized User, Contact Admin to Assign Permission to You']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['status' => 'success', 'user' => auth()->user(), 'access_token' => $accessToken, 'permissions' => $permissions, 'role' => $role]);

    }

	public function login(Request $request)
    {

        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
        	//$error['message'] = "Invalid Credentials";
            return response(['status' => 'failed', 'message' => 'Invalid Credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['status' => 'success', 'user' => auth()->user(), 'access_token' => $accessToken]);

    }

    public function mail($subject,$name, $email)
	{
	   $data = array("subject" => $subject, "name" => $name , "email" => base64_encode($email));
	   Mail::to($email)->send(new Activitymail($data));
	   
	   return true;
	}

	public function email_link(Request $request, $email)
	{
		if(env('APP_ENV') != 'local')
			$this->mail("Verify Account", $request->user()->name, $email);

		return response(['status' => 'success', 'message' => 'Verification Link is Sent to your Email']);
	}

	public function verify_email(Request $request, $email)
	{
		$user = UserDetails::where(['email' => $email])->first();
		if($user == null)
		{
			$error['message'] = "User not Found";
			return response(['status' => 'failed', 'error' => $error]); 
		}

		$user->Is_email_verified = 1;
		$user->date_email_verified = date('Y-m-d');
		$user->save();

		return response(['status' => 'success', 'message' => 'Email is Verified']);
	}

	public function verify_phone(Request $request, $code)
	{

		$user = UserDetails::where(['user_id' => $request->user()->id])->first();

		if($user == null)
		{
			$error['message'] = "User not Found";
			return response(['status' => 'failed', 'error' => $error]); 
		}

		$verify = Verification::where(['type' => 'Phone Number' ,'to_verify' => $user->mobile1, 'code' => $code])->first();


		if($verify == null)
		{
			$error['message'] = "Invalid Code or Phone Number";
			return response(['status' => 'failed', 'error' => $error]); 
		}

		$verify = UserDetails::where(['mobile1' => $verify->to_verify])->first();

		$verify->Is_phone_number_verified = 1;
		$verify->date_phone_number_verified = date('Y-m-d');
		$verify->save();

		return response(['status' => 'success', 'message' => 'Phone is Verified']);
	}

	public function sms(Request $request)
	{

		// Create a new cURL resource
		$url = 'https://api.africastalking.com/version1/messaging';

		$user = UserDetails::where(['user_id' => $request->user()->id])->first();
		if($user == null)
		{
			$error['message'] = "User not Found";
			return response(['status' => 'failed', 'error' => $error]); 
		}

		$ch = curl_init($url);

		$code = uniqid();
		$insert['to_verify'] = $user->mobile1;
		$insert['code'] = $code;
		$insert['type'] = 'Phone Number';

		// Setup request to send json via POST
		$data = [
		    'username' => 'surebank',
		    'to' => $user->mobile1,
		    'message' => "This is your secret code ".$code.""
		];

		$res = Verification::Create($insert);

		$payload = json_encode(['data'=>$data]);

		// Return response instead of outputting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Attach encoded JSON string to the POST fields
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

		// Set the content type to application/json
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'apiKey: c9233d0c30f63a534aa1ed49265d7e57055a8d6eccc79bd8a761012ad231ba78',
		    'Content-Type: application/x-www-form-urlencoded',
		    'Accept: application/json'
		));

		

		// Execute the POST request
		$result = curl_exec($ch);

		// Close cURL resource
		curl_close($ch);

		$result = json_decode($result);

		$message = "Secret Code sent to your Phone Number";

		if($result->SMSMessageData->Recipients[0]->statusCode != '101')
		{
			$message = $result->SMSMessageData->Recipients[0]->status;
		}

		return response(['status' => 'success', 'message' => $message]);
	}

	public function codes()
	{
		$path = storage_path() ."/json/phone.json";
		$json = json_decode(file_get_contents($path),true);
		return response(['status' => 'success', 'codes' => $json]);
	}
    
}
