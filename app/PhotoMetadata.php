<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhotoMetadata extends Model {

    protected $table            = 'photo_metadata';
    protected $guarded          = ['id'];
    protected $fillable         = [];
    public $timestamps          = false;

    public function photo()
    {
        return $this->belongsTo('Photo');
    }
}
