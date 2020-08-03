<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserHomeAddress extends Model
{
     protected $fillable = ['address','country_id','state_id','user_id'];
}
