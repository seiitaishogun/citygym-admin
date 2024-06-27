<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Models;

use Illuminate\Database\Eloquent\Model;

class BannerMeta extends Model {
    protected $table = 'banner_meta';
    protected $primaryKey = 'meta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'banner_id',
        'title',
        'link_type',
        'display_url',
        'content',
        'image',
        'lang_code'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * get category object
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function banner() {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    /**
     * convert json image data to object
     * @param $value
     * @return mixed
     */
    public function getImageAttribute($value) {
        if ( isset($value) && !empty($value) ) return $this->attributes['image'] = ( is_object ($value) ) ? $value : json_decode($value);
    }

    public static function updateMeta(array $metaData) {
        $bannerMeta = self::where('banner_id', $metaData['banner_id'])
            ->where('lang_code', $metaData['lang_code']??'vn')
            ->first();

        if ( isset($bannerMeta->meta_id) ) {
            if ( isset($metaData['image']) && is_object($metaData['image']) ) $metaData['image'] = json_encode($metaData['image']);
            $bannerMeta->update($metaData);
        } else {
            $metaData['image'] = json_encode($metaData['image']);
            self::create($metaData);
        }
    }
}
