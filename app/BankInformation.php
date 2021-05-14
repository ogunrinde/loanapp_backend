<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankInformation extends Model
{
    protected $fillable = ['accountnumber','bvn','bankname','user_id', 'bankcode','Is_BVN_verified'];
}
