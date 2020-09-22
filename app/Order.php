<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Order
 *
 * @property int $id
 * @property string $name
 * @property int $woo_id
 * @property int $disabled
 * @property string|null $disabled_at
 * @property int|null $labworks_id
 * @property int $customer_id
 * @property string|null $woo_status
 * @property string|null $labworks_status
 * @property int $has_prints
 * @property int $photos_cached
 * @property int|null $photo_count
 * @property int|null $expected_photo_count
 * @property string|null $photos_cached_at
 * @property int $queued_for_import
 * @property int|null $import_attempts
 * @property int $xml_created
 * @property string $tracking_id
 * @property int $has_been_imported
 * @property int $uses_roll_folders
 * @property int $offline
 * @property string|null $expiring_email_sent_at
 * @property string|null $expired_email_sent_at
 * @property int $thumbnails_regenerated
 * @property int $thumbnails_regeneration_queued
 * @property int $thumbnails_regenerated_failed
 * @property string $ordered_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDisabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpectedPhotoCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpiredEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpiringEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereHasBeenImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereHasPrints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereImportAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLabworksId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLabworksStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOffline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePhotoCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePhotosCached($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePhotosCachedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereQueuedForImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereThumbnailsRegenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereThumbnailsRegeneratedFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereThumbnailsRegenerationQueued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTrackingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUsesRollFolders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereWooId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereWooStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereXmlCreated($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable         = [];
    protected $guarded          = ['id','created_at'];
}
