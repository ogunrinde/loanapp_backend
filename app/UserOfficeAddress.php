<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOfficeAddress extends Model
{
    protected $fillable = ['address','country_id','state_id','user_id','employmentstatus','company_name','contact_number','company_website'];
}
