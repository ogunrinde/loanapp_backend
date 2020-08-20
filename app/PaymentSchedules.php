<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedules extends Model
{
    protected $fillable = [
    	'borrower_id', 
    	'lender_id',
    	'borrower_request_id',
    	'expected_amount_to_paid',
    	'dueDate',
    	'status'
    ];

     public function request()
    {
        return $this->belongsTo('App\MakeRequest','borrower_request_id','id');
    }


    public function lender()
    {
        return $this->belongsTo('App\User','lender_id','id');
    }

    public function borrower()
    {
        return $this->belongsTo('App\User','borrower_id','id');
    }
}
