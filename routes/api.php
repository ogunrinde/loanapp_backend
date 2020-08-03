<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Route::get('example', array('middleware' => 'cors', 'uses' => 'ExampleController@dummy'));

Route::group(['middleware' => 'cors'], function(){


	Route::post('/register','Api\AuthController@register');
	Route::post('/login', 'Api\AuthController@login');
	Route::get('/countries', 'Api\CountriesController@index');
	Route::get('/state/{id}', 'Api\StatesController@index');


	Route::middleware('auth:api')->get('/user', function (Request $request) {
	    //return $request->user();
	});


	Route::middleware('auth:api')->group( function(){
	   Route::get('/userdetails', 'Api\UserDetailsController@index');
	   Route::post('/storeuserdetails', 'Api\UserDetailsController@store');
	   Route::post('/userHomeAddress', 'Api\UserHomeAddressController@store');
	   Route::post('/userOfficeAddress', 'Api\UserOfficeAddressController@store');
	   Route::post('/userSocialMediaAccounts', 'Api\UserSocialMediaAccountController@store');
	   Route::post('/loanrequest', 'Api\MakeRequestController@store');
	   Route::get('/result', 'Api\MakeRequestController@index');
	   Route::post('/supplyloan', 'Api\SureVaultController@store');
	   Route::post('/connectborrowerToLender', 'Api\ConnectBorrowerToLenderController@store');
	   Route::get('/getprofile/{id}', 'Api\UserController@getprofile');
	   Route::get('/getvault/{vaultId}', 'Api\SureVaultController@getvault');
	   Route::get('/getuserloanrequest', 'Api\UserController@getuserloanrequest');
	   Route::get('/getLenderpendingloanapprovals','Api\LoansController@getLenderpendingloanapprovals');
	   Route::get('/getLenderapprovedLoan','Api\LoansController@getLenderapprovedLoan');
	   Route::post('/updateloanapprovalstatus','Api\LoansController@updateloanapprovalstatus');
	   Route::get('/getLoanToBeDisbursed','Api\LoansController@getLoanToBeDisbursed');
	   Route::post('/updatesuredeal','Api\SureDealsController@store');
	   Route::get('/getLoansDisbursed','Api\LoansController@getLoanseDisbursed');
	   Route::get('/getBorrowerapprovedloan','Api\LoansController@getBorrowerapprovedloan');
	   Route::get('/getBorrowerpendingloan','Api\LoansController@getBorrowerpendingloanapprovals');
	   Route::get('/useractivitiesanalytics','Api\UserController@analytics');
	   Route::get('/getcompleteuserprofile', 'Api\UserController@getcompleteuserprofile');
	   Route::post('/bankinfo', 'Api\BankAccountController@store');
	   Route::get('/getvault','Api\SureVaultController@index');
	   Route::get('/gettransaction/{vaultId}','Api\SureVaultController@gettransaction');
	   Route::post('/peer','Api\MakeRequestController@peer');
	   Route::get('/getborrowerallloansrequest','Api\UserController@getborrowerallloansrequest');
	   Route::post('/getLendersForBorrower','Api\MakeRequestController@getLendersForBorrower');
	   Route::post('/storerepayment','Api\LoanrepaymentController@store');
	   Route::get('/repayments_lender','Api\LoanrepaymentController@repayments_lender');
	   Route::get('/repayments_borrower','Api\LoanrepaymentController@repayments_borrower');
	   Route::get('/surelenderoffers','Api\LoansController@surelenderoffers');
	   Route::get('/sureborroweroffers','Api\LoansController@sureborroweroffers');
	   Route::post('/connectwithborrower', 'Api\LoansController@connectwithborrower');
	});

});
