<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class permissions extends Model
{
    protected $fillable = ["doc_type","role_id","view","edit","cancel","deactivate", "approval", "create"];
}
