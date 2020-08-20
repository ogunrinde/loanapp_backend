<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Countries;
use App\MakeRequest;
use App\SureVault;
use App\suredeals;
use App\UserDetails;
use App\UserHomeAddress;
use App\UserOfficeAddress;
use App\UserSocialMediaAccounts;
use App\BankInformation;

class UserController extends Controller
{
    public function getprofile(Request $request,$id)
    {
    	$data = $request->all();
        $home = null;
        $office = null;
        $user = User::where(['id'=>$id])->with(['userdetails','homeaddress','officeaddress','socialmedia','bankdetails'])->first();

        if(isset($user->homeaddress))
        {
            $home = UserHomeAddress::with(['userhomecountry','userhomestate'])->where(['user_id' => $user->homeaddress->user_id])->first();
        }
        if(isset($user->officeaddress))
        {
            $office = UserOfficeAddress::with(['userofficecountry','userofficestate'])->where(['user_id' => $user->officeaddress->user_id])->first();
        }


         return response(['status' => 'success', 'userprofile' => $user, 'homeaddress' => $home, 'officeaddress' => $office]);
    }

    public function getuserloanrequest(Request $request)
    {
    	$data = $request->all();

    	//$loanrequest = 

        $loanrequest = MakeRequest::where(['user_id'=> $request->user()->id])->orderBy('id','DESC')->first();


        $matchingoffers = [];
        if($loanrequest != null)
        {
        	$matchingoffers = SureVault::where('maxRequestAmount', '>=', $loanrequest->requestAmount)
                                    ->where('minInterestperMonth', '<=',$loanrequest->maxInterestRate)
                                    ->with('user')->get();
        }

        return response(['status' => 'success', 'loanrequest' => $loanrequest, 'offers' => $matchingoffers]);
        
    }

    public function analytics(Request $request)
    {
        $surevault = SureVault::where(['user_id' => $request->user()->id])->get();
        $makerequest = MakeRequest::where(['user_id' => $request->user()->id])->get();
        $suredeals = suredeals::where(['lender_id' => $request->user()->id])->get();
        $disbursed = suredeals::where(['lender_id' => $request->user()->id, 'Is_cash_disbursed' => '1'])->get();

        $res = array("suredeals" => $suredeals->count(), "disbursed" => $disbursed->count(), "loanrequest" => $makerequest->count(), "surevault" => $surevault->count());

        return response(['status' => 'success', 'analytics' => $res]);
    }

    public function getcompleteuserprofile(Request $request)
    {
        $userdetails = UserDetails::where(['user_id' => $request->user()->id])->first();
        $homeaddress = UserHomeAddress::where(['user_id' => $request->user()->id])->first();
        $officeaddress = UserOfficeAddress::where(['user_id' => $request->user()->id])->first();
        $socialmedia = UserSocialMediaAccounts::where(['user_id' => $request->user()->id])->first();
        $bankdetails = BankInformation::where(['user_id' => $request->user()->id])->first();

        return response()->json(['status' => 'success', 'userdetails' => $userdetails, 'homeaddress' => $homeaddress,'officeaddress'=>$officeaddress, 'socialmedia' => $socialmedia, 'bankdetails' => $bankdetails]);

    }

    public function getborrowerallloansrequest(Request $request)
    {
        $requests = MakeRequest::with('connect')->where(['user_id' => $request->user()->id])->get();
        return response()->json(['status' => 'success', 'requests' => $requests]);
    }

    
}
