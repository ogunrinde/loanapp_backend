<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MakeRequest extends Model
{
    protected $fillable = ['requestAmount','loanperiod','maxInterestRate','minInterestRate','repaymentplan','requiredcreditBereau','lender_country_id','lender_state_id','user_id','lender_city_id','request_type'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function connect()
    {
    	return $this->hasOne('App\ConnectBorrowerToLender','borrower_request_id','id');
    }
}
