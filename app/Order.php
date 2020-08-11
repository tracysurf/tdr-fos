<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable         = [];
    protected $guarded          = ['id','created_at'];
}
