<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\City;

class CityController extends Controller
{
    public function index($id)
    {
    	$cities = City::where(['state_id'=> $id])->get();
        return response(['status' => 'success', 'cities' => $cities]);
    }
}
