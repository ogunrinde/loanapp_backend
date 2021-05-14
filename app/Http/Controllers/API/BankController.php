<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BankCode;

class BankController extends Controller
{
    public function banks(Request $request)
    {
    	$banks = BankCode::all();
    	return response(['status' => 'success', 'banks' => $banks]);
    }
}
