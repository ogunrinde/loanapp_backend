<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;

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

	    $user = User::create($data);

	    $accessToken = $user->createToken('authToken')->accessToken;

	    return response(['status' => 'success', 'user' => $user, 'access_token' => $accessToken]);
	}

	public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['status' => 'failed', 'message' => 'Invalid Credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['status' => 'success', 'user' => auth()->user(), 'access_token' => $accessToken]);

    }
    
}
