<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repayments;

class RepaymentController extends Controller
{
    public function index(Request $request)
    {
    	$repayments = Repayments::with(['borrower', 'lender','request'])->where('lender_id','=' ,$request->user()->id)->where('IsWithdrawn','=', 0)->get();
    	return response(['status' => 'success', 'repayments' => $repayments]);
    }
}
