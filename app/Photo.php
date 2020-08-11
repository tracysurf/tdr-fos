<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{

    public function thumbnailURL($size = '_social')
    {
        $cdn = env('DO_SPACES_URL');

        $thumbnail_path = $this->thumbnail_path;
        if($size !== '_social')
        {
            $thumbnail_path = str_replace('_social', $size, $thumbnail_path);
        }

        return $cdn. '/' . $thumbnail_path;
    }
}
