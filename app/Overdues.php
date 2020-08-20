<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Overdues extends Model
{
    protected $fillable = [
    	"paymentschedule_id",
    	"borrower_id",
    	"lender_id",
    	"borrower_request_id",
    	"ScheduledPaymentdate"
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

    public function paymentschedule()
    {
    	return $this->belongsTo('App\PaymentSchedules','paymentschedule_id','id');
    }
}
