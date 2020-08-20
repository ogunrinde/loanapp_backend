<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SureVault extends Model
{
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
        'vault_creation_type'
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }
 
  
}
