<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attach extends Model {
    protected $table = 'attach';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name', 'file_title', 'file_type'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [ 'user_id' ];
}
