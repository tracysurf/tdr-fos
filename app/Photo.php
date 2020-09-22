<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Photo
 *
 * @property int $id
 * @property int|null $order_id
 * @property string $filename
 * @property string $extension
 * @property int|null $size
 * @property string|null $roll
 * @property string|null $roll_name
 * @property string|null $folder
 * @property int $favorite
 * @property string|null $thumbnail_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Photo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Photo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Photo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereFavorite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereRoll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereRollName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Photo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
