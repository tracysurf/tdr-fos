<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $dates            = [
        'created_at',
        'updated_at',
        'seen_at',
        'sent_at'
    ];
}
