<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserHomeAddress extends Model
{
     protected $fillable = ['address','country_id','state_id','user_id', 'city_id'];

    public function userhomecountry()
    {
        return $this->BelongsTo('App\Countries','country_id','id');
    }

     public function userhomestate()
    {
        return $this->BelongsTo('App\States','state_id','id');
    }

}
