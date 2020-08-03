<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
     protected $guard = [];

     protected $fillable = ['surname','firstname','middlename','gender','date_of_birth','mobile1','mobile2', 'user_id', 'email'];

     public function user()
    {
        return $this->belongsTo('App\User');
    }
}
