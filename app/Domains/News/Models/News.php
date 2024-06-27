<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */

namespace App\Domains\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class News extends Model{
    use SoftDeletes;

    protected $table = 'news';
    protected $primaryKey = 'new_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cat_id',
        'status',
        'is_url_display',
        'news_type'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'author_id',
        'author_type'
    ];

    /**
     * format created_at for view
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function getCreatedDateAttribute() {
        return $this->attributes['created_date'] = (new Carbon($this->created_at))->format('d/m/Y');
    }

    /**
     * Get the category
     */
    public function getCategoryAttribute()
    {
        return $this->attributes['category'] = (new Category())->getNameByLocate($this->cat_id, app()->getLocale());
    }

    /**
     * get meta data
     * @return mixed
     */
    public function getMetaAttribute() {
        $lang = app()->getLocale() ?? 'vn';

        $metaData = NewsMeta::where('lang_code', $lang)
            ->where('new_id', $this->new_id)->first();
        return $this->attributes['meta'] = $metaData;
    }

    public function getImageAttribute($value) {
        return $this->attributes['image'] = ( is_object ($value) && !is_array($value) ) ? $value : json_decode($value);
    }

    public function getUpdatedAtAttribute($date)
    {
        //return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y');
        return (new Carbon($date))->format('d/m/Y');
    }

}
