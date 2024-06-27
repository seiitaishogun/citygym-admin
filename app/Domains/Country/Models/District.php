<?php
/**
 * @author tmtuan
 * created Date: 12-Nov-20
 */

namespace App\Domains\Country\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model{
    // use SoftDeletes;
    public $timestamps = false;
    protected $table = 'country_district';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'province_id',
        'name',
        'value',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
    ];
}