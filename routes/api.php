<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Mail\Activitymail;
use Illuminate\Support\Facades\Mail;

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

	Route::post('/adminregister','Api\AuthController@adminregister');

	Route::post('/adminlogin','Api\AuthController@adminlogin');


	Route::post('/login', 'Api\AuthController@login');
	Route::get('/countries', 'Api\CountriesController@index');
	Route::get('/state/{id}', 'Api\StatesController@index');
	Route::get('/city/{id}', 'Api\CityController@index');
	Route::get('/surelenderoffers','Api\LoansController@surelenderoffers');
	Route::get('/sureborroweroffers','Api\LoansController@sureborroweroffers');
	Route::get('/mail', 'Api\AuthController@mail');
	Route::put('/verify_email/{email}', 'Api\AuthController@verify_email');
	Route::get('/countrycodes','Api\AuthController@codes');
	
	Route::middleware('auth:api')->get('/user', function (Request $request) {
	    //return $request->user();
	});


	Route::middleware('auth:api')->group( function(){

		Route::get('/okay', function() {
	       return response(['status' => 'success']);
	 	})->middleware('permission:Loan Request:create');


       Route::post('/role', 'Api\RolesController@store')->middleware('permission:User Management:create');
       Route::get('/admins', 'Api\AdminController@index');
       Route::post('/admin','Api\AdminController@store')->middleware('permission:User Management:create');
       Route::get('/roles', 'Api\RolesController@index')->middleware('permission:User Management:view');
       //Route::post('/role', 'Api\RolesController@store')->middleware('permission:User Management:create');
       Route::post('/permission', 'Api\PermissionController@store')->middleware('permission:User Management:create');
       Route::get('/permissions/{id}','Api\PermissionController@permissions')->middleware('permission:User Management:view');

       Route::post('/userrole','Api\AdminController@userrole')->middleware('permission:User Management:create');
       Route::get('/userrole/{id}','Api\AdminController@getuserrole')->middleware('permission:User Management:view');

       Route::get('/sureusers', 'Api\AdminController@users')->middleware('permission:User Management:view');

       Route::get('/getallvault','Api\AdminController@getallvault')->middleware('permission:Vault:view');
       Route::get('/getallloansrequest','Api\AdminController@getallloansrequest')->middleware('permission:Loan Request:view');
       Route::get('/getdeal/{id}', 'Api\AdminController@getdeal')->middleware('permission:Loan Request:view');
       Route::get('/paymentschedules/{id}', 'Api\AdminController@paymentschedules')->middleware('permission:Loan Request:view');;
       Route::get('/activitiesanalytics','Api\AdminController@activitiesanalytics')->middleware('permission:User Management:view');

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
	   Route::get('/getLenderpendingloanapprovals', 'Api\LoansController@getLenderpendingloanapprovals');
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
	   Route::post('/connectwithborrower', 'Api\LoansController@connectwithborrower');
	   Route::post('/peerlendertoborrower', 'Api\SureVaultController@peerlendertoborrower');
	   Route::get('/overdueforlender', 'Api\OverdueController@overdueforlender');
	   Route::get('/overdueforborrower', 'Api\OverdueController@overdueforborrower');
	   Route::get('/replaymentstructure','Api\SureDealsController@replaymentstructure');
	   Route::get('/getborrowersLoansReceived', 'Api\LoansController@getborrowersLoansReceived');
	   Route::get('/borrower_payment_schedules/{id}','Api\PaymentSchedulesController@borrower_payment_schedules');
	   Route::get('/lender_payment_schedules/{id}','Api\PaymentSchedulesController@lender_payment_schedules');
	   Route::post('/verify_phone/{code}', 'Api\AuthController@verify_phone');
	   Route::get('/sms', 'Api\AuthController@sms');
	   Route::get('/email_link/{email}','Api\AuthController@email_link');
	   Route::post('/updateofferrequeststatus','Api\LoansController@updateofferrequeststatus');

	});

});
