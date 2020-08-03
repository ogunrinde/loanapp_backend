<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSocialMediaAccounts extends Model
{
    protected $fillable = ['facebook','instagram','twitter','linkedin','user_id'];
}
