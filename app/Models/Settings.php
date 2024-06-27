<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group',
        'item',
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
