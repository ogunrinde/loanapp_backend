<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\permissions;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
    	$permissions = permissions::all();
    	return response(['status' => 'success', 'permissions' => $permissions]);
    }

    public function permissions(Request $request, $id)
    {
    	$permissions = permissions::where(['role_id' => $id])->get();
    	return response(['status' => 'success', 'permissions' => $permissions]);
    }

    public function store(Request $request)
    {
    	$data = $request->all();
        $validator = Validator::make($data, [
            'role_id' => 'required|numeric'
        ]);

        if($validator->fails()) { 
            return response()->json(['status' => 'failed', 'error'=>$validator->errors()]);            
        }

    	//$data = [];

    	$res = json_decode($request->permission);
    	for ($r = 0; $r < count($res);$r++) {
    		$values = array("role_id" => $request->role_id, "doc_type" => $res[$r]->doc_type, "view" => $res[$r]->view, "edit" => $res[$r]->edit, "cancel" => $res[$r]->cancel, "deactivate" => $res[$r]->deactivate);
    		$response = permissions::updateOrCreate(['role_id' => $request->role_id, 'doc_type' => $res[$r]->doc_type],$values);
    	}
        return response(['status' => 'success']);
    }
}
