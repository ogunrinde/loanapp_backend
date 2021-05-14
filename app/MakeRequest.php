<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MakeRequest extends Model
{

	use SoftDeletes;


    protected $fillable = ['requestAmount','loanperiod','maxInterestRate','minInterestRate','repaymentplan','requiredcreditBereau','borrower_country_id','borrower_state_id','user_id','borrower_city_id','request_type'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function connect()
    {
    	return $this->hasOne('App\ConnectBorrowerToLender','borrower_request_id','id');
    }
}
