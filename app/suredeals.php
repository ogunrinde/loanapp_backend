<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class suredeals extends Model
{
     protected $fillable = [
    	'lender_borrower_connection_id',
    	'lender_id',
    	'borrower_id',
    	'request_id',
    	'LoanID'
    ];

    public function connect()
    {
    	return $this->belongsTo('App\ConnectBorrowerToLender','lender_borrower_connection_id','id');
    }

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
        return $this->belongsTo('App\MakeRequest','request_id','id');
    }

}
