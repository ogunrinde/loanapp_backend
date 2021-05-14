<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SureVault extends Model
{

    use SoftDeletes;


    protected $fillable = [
    	'fundamount',
    	'availablefrom',
    	'availableto',
    	'maxRequestAmount',
    	'minRequestAmount',
    	'minInterestperMonth',
    	'maxInterestperMonth',
    	'borrower_country_id',
    	'borrower_state_id',
    	'email_must_be_verified',
    	'phonenumber_must_be_verified',
    	'bvn_must_be_verified','user_id',
        'minloantenor',
        'maxloantenor',
        'borrower_city_id',
        'vault_creation_type',
        'creditbureau_report_required'

    ];

    protected $dates = ['deleted_at'];


    public function user()
    {
        return $this->belongsTo('App\User');
    }
 
  
}
