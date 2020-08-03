<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserHomeAddress;
use Illuminate\Support\Facades\Validator;

class UserHomeAddressController extends Controller
{
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
                'address' => 'required',
                'country_id' => 'required|numeric',
                'state_id' => 'required|numeric'
            ]);

            if($validator->fails()) { 
                return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
            }

            $data['user_id'] = $request->user()->id;

            $res = UserHomeAddress::updateOrCreate(['user_id' => $request->user()->id],$data);

           return response(['status' => 'success', 'userHomeAddress' => $res]);
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
