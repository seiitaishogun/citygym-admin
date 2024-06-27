<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model {
    use SoftDeletes;

    protected $table = 'banner';
    protected $primaryKey = 'banner_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status', 'order'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'user_id',
    ];

    /**
     * @var array
     */
//    protected $appends = [
//        'meta',
//    ];

    /**
     * Get the meta data for this banner
     */
    public function getMeta($lang)
    {
        $lang = $lang ?? app()->getLocale();
        $metaData = BannerMeta::where('lang_code', $lang)
                ->where('banner_id', $this->banner_id)->first();
        $this->attributes['meta'] = $metaData;
        return $this;
    }

    /**
     * get banner name by lang
     * @param $banner_id
     * @param string $lang
     * @return mixed
     */
    public function getNameByLobannere($lang = 'vn') {
        $banner = BannerMeta::where('banner_id', $this->banner_id)
                        ->where('lang_code', $lang)
                        ->first();
        return $banner->title;
    }

    /**
     * format created_at for view
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function getCreatedDateAttribute() {
        return $this->attributes['created_date'] = (new Carbon($this->created_at))->format('d/m/Y');
    }

    public function getUpdatedAtAttribute($date)
    {
        //return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y');
        return (new Carbon($date))->format('d/m/Y');
    }

    public function getImageAttribute( $value ) {
        return $this->attributes['image'] = ( is_object ($value) ) ? $value : json_decode($value);
    }

}
