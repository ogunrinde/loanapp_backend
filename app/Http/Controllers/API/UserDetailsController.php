<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\UserDetails;
use Illuminate\Support\Facades\Validator;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;

class UserDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userdetails = User::find($request->user()->id);
            
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
            'surname' => 'required|max:55',
            'firstname' => 'required',
            'middlename' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'email'=>'required|email',
            'mobile1'=>'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()], 401);            
        }

        $data['email'] = $request->user()->email;
        $data['user_id'] = $request->user()->id;

        if(env('APP_ENV') != 'local')
            $this->mail("Verify Account", $request->user()->name, $request->user()->email);

        $res = UserDetails::updateOrCreate(['user_id' => $request->user()->id],$data);

       return response(['status' => 'success', 'userdetails' => $res]);
    }

    public function mail($subject,$name, $email)
    {
       $data = array("subject" => $subject, "name" => $name , "email" => base64_encode($email));
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
