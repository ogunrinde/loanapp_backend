<?php

namespace App\Http\Controllers\Api;
use App\roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\permissions;

class RolesController extends Controller
{
    public function index(Request $request)
    {
    	$roles = roles::all();
    	return response(['status' => 'success', 'roles' => $roles]);
    }

    

    public function store(Request $request)
    {
    	 $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|unique:roles'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

        $role = roles::Create($data);

        $roles = roles::all();
        return response(['status' => 'success', 'roles' => $roles]);
    }
}
