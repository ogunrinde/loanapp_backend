<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectBorrowerToLender extends Model
{

     use SoftDeletes;


     protected $fillable = [
    	'lender_id',
    	'borrower_id',
    	'sure_vault_id',
    	'borrower_request_id',
        'connection_type'
    ];

    public function borrower()
    {
        return $this->belongsTo('App\User','borrower_id','id');
    }

    public function lender()
    {
        return $this->belongsTo('App\User','lender_id','id');
    }

    public function request()
    {
        return $this->belongsTo('App\MakeRequest','borrower_request_id','id');
    }

    public function surevault()
    {
    	return $this->belongsTo('App\SureVault','sure_vault_id','id');
    }



   
}
