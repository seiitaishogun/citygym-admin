<?php
/**
 * @author tmtuan
 * created Date: 16-Nov-20
 */

namespace App\Domains\News\Models;

use Illuminate\Database\Eloquent\Model;

class NewsMeta extends Model{
    protected $table = 'news_meta';
    protected $primaryKey = 'meta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'new_id',
        'title',
        'slug',
        'content',
        'display_url',
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
     * convert json image data to object
     * @param $value
     * @return mixed
     */
    public function getImageAttribute($value) {
        return $this->attributes['image'] = ( is_object ($value) ) ? $value : json_decode($value);
    }
}
