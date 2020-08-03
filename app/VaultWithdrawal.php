<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VaultWithdrawal extends Model
{
    protected $fillable = [
    	"Amount_withdrawn",
    	"make_request_id",
    	"user_id",
    	"sure_vault_id"
    ];

    public function request()
    {
        return $this->hasOne('App\MakeRequest');
    }
}
