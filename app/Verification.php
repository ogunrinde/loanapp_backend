<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
    	"to_verify",
    	"code",
    	"type"
    ];
}
