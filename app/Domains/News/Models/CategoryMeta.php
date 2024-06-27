<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

namespace App\Domains\News\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryMeta extends Model {
    protected $table = 'category_meta';
    protected $primaryKey = 'meta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cat_id',
        'cat_name',
        'slug',
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
    public function category() {
        return $this->belongsTo(Category::class, 'cat_id');
    }
}
