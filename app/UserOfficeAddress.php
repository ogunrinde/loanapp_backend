<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOfficeAddress extends Model
{
    protected $fillable = ['address','country_id','state_id','user_id','employmentstatus','company_name','contact_number','company_website','city_id'];

    public function userofficecountry()
    {
        return $this->BelongsTo('App\Countries','country_id','id');
    }

     public function userofficestate()
    {
        return $this->BelongsTo('App\States','state_id','id');
    }

    public function city()
    {
        return $this->BelongsTo('App\City','city_id','id');
    }
}
