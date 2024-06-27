<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model {
    use SoftDeletes;

    protected $table = 'category';
    protected $primaryKey = 'cat_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'status',
        'thutu'
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
     * Get the meta data for this category
     */
    public function meta()
    {
        return $this->hasMany('App\Domains\News\Models\CategoryMeta', 'cat_id');
    }

    /**
     * get category name by lang
     * @param $cat_id
     * @param string $lang
     * @return mixed
     */
    public function getNameByLocate($cat_id, $lang = 'vn') {
        $cat = CategoryMeta::where('cat_id', $cat_id)
                        ->where('lang_code', $lang)
                        ->first();
        return $cat->cat_name;
    }

    /**
     * get parent category object
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent() {
        return $this->belongsTo('App\Domains\News\Models\Category', 'parent_id');
    }

    /**
     * get parent cateogry name by lang
     * @param string $lang
     * @return mixed
     */
    public function getParentName($lang = 'vn') {
        $parent_id = $this->parent->cat_id;
        //\DB::connection()->enableQueryLog();
        $cat = Category::join('category_meta', 'category.cat_id', '=', 'category_meta.cat_id' )
            ->where('category_meta.lang_code', $lang)
            ->where('category.cat_id', $parent_id)
            ->where('status', 'published')
            ->where('deleted_at', NULL)
            ->first();
        //dd(\DB::getQueryLog());
        return $cat->cat_name;
    }

    /**
     * format created_at for view
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function getCreatedAtAttribute( $value ) {
        return $this->attributes['created_at'] = (new Carbon($value))->format('d/m/Y');
    }
}
